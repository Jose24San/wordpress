<?php



class Zip {

	const VERSION = 1.27;



	const ZIP_LOCAL_FILE_HEADER = "\x50\x4b\x03\x04"; // Local file header signature

	const ZIP_CENTRAL_FILE_HEADER = "\x50\x4b\x01\x02"; // Central file header signature

	const ZIP_END_OF_CENTRAL_DIRECTORY = "\x50\x4b\x05\x06\x00\x00\x00\x00"; //end of Central directory record

	

	const EXT_FILE_ATTR_DIR = "\x10\x40\xed\x41"; // Unix : Dir + mod:755

	const EXT_FILE_ATTR_FILE = "\x00\x40\xa4\x81"; // Unix : File + mod:644



	const ATTR_VERSION_TO_EXTRACT = "\x0A\x00"; // Version needed to extract

	const ATTR_MADE_BY_VERSION = "\x15\x03"; // Made By Version 

	

	private $zipMemoryThreshold = 1048576; // Autocreate tempfile if the zip data exceeds 1048576 bytes (1 MB)



	private $zipData = NULL;

	private $zipFile = NULL;

	private $zipComment = NULL;

	private $cdRec = array(); // central directory

	private $offset = 0;

	private $isFinalized = FALSE;



	private $streamChunkSize = 65536;

	private $streamFilePath = NULL;

	private $streamTimeStamp = NULL;

	private $streamComment = NULL;

	private $streamFile = NULL;

	private $streamData = NULL;

	private $streamFileLength = 0;



	/**

	 * Constructor.

	 *

	 * @param $useZipFile boolean. Write temp zip data to tempFile? Default FALSE

	 */

	function __construct($useZipFile = FALSE) {

		if ($useZipFile) {

			$this->zipFile = tmpfile();

		} else {

			$this->zipData = "";

		}

	}



	function __destruct() {

		if (!is_null($this->zipFile)) {

			fclose($this->zipFile);

		}

		$this->zipData = NULL;

	}



	/**

	 * Set Zip archive comment.

	 *

	 * @param String $newComment New comment. NULL to clear.

	 * @return bool $success

	 */

	public function setComment($newComment = NULL) {

		if ($this->isFinalized) {

			return FALSE;

		}

		$this->zipComment = $newComment;



		return TRUE;

	}



	/**

	 * Set zip file to write zip data to.

	 * This will cause all present and future data written to this class to be written to this file.

	 * This can be used at any time, even after the Zip Archive have been finalized. Any previous file will be closed.

	 * Warning: If the given file already exists, it will be overwritten.

	 *

	 * @param String $fileName

	 * @return bool $success

	 */

	public function setZipFile($fileName) {

		if (file_exists($fileName)) {

			unlink ($fileName);

		}

		$fd=fopen($fileName, "x+b");

		if (!is_null($this->zipFile)) {

			rewind($this->zipFile);

			while(!feof($this->zipFile)) {

				fwrite($fd, fread($this->zipFile, $this->streamChunkSize));

			}

				

			fclose($this->zipFile);

		} else {

			fwrite($fd, $this->zipData);

			$this->zipData = NULL;

		}

		$this->zipFile = $fd;



		return TRUE;

	}



	/**

	 * Add an empty directory entry to the zip archive.

	 * Basically this is only used if an empty directory is added.

	 *

	 * @param String $directoryPath  Directory Path and name to be added to the archive.

	 * @param int    $timestamp      (Optional) Timestamp for the added directory, if omitted or set to 0, the current time will be used.

	 * @param String $fileComment    (Optional) Comment to be added to the archive for this directory. To use fileComment, timestamp must be given.

	 * @return bool $success

	 */

	public function addDirectory($directoryPath, $timestamp = 0, $fileComment = NULL) {

		if ($this->isFinalized) {

			return FALSE;

		}

		$this->buildZipEntry($directoryPath, $fileComment, "\x00\x00", "\x00\x00", $timestamp, "\x00\x00\x00\x00", 0, 0, self::EXT_FILE_ATTR_DIR);



		return TRUE;

	}



	/**

	 * Add a file to the archive at the specified location and file name.

	 *

	 * @param String $data        File data.

	 * @param String $filePath    Filepath and name to be used in the archive.

	 * @param int    $timestamp   (Optional) Timestamp for the added file, if omitted or set to 0, the current time will be used.

	 * @param String $fileComment (Optional) Comment to be added to the archive for this file. To use fileComment, timestamp must be given.

	 * @return bool $success

	 */

	public function addFile($data, $filePath, $timestamp = 0, $fileComment = NULL)   {

		if ($this->isFinalized) {

			return FALSE;

		}



		$gzType = "\x08\x00"; // Compression type 8 = deflate

		$gpFlags = "\x02\x00"; // General Purpose bit flags for compression type 8 it is: 0=Normal, 1=Maximum, 2=Fast, 3=super fast compression.

		$dataLength = strlen($data);

		$fileCRC32 = pack("V", crc32($data));



		$gzData = gzcompress($data);

		$gzData = substr( substr($gzData, 0, strlen($gzData) - 4), 2); // gzcompress adds a 2 byte header and 4 byte CRC we can't use.

		// The 2 byte header does contain useful data, though in this case the 2 parameters we'd be interrested in will always be 8 for compression type, and 2 for General purpose flag.

		$gzLength = strlen($gzData);



		if ($gzLength >= $dataLength) {

			$gzLength = $dataLength;

			$gzData = $data;

			$gzType = "\x00\x00"; // Compression type 0 = stored

			$gpFlags = "\x00\x00"; // Compression type 0 = stored

		}



		if (is_null($this->zipFile) && ($this->offset + $gzLength) > $this->zipMemoryThreshold) {

			$this->zipFile = tmpfile();

			fwrite($this->zipFile, $this->zipData);

			$this->zipData = NULL;

		}



		$this->buildZipEntry($filePath, $fileComment, $gpFlags, $gzType, $timestamp, $fileCRC32, $gzLength, $dataLength, self::EXT_FILE_ATTR_FILE);

		if (is_null($this->zipFile)) {

			$this->zipData .= $gzData;

		} else {

			fwrite($this->zipFile, $gzData);

		}

		return TRUE;

	}



	/**

	 * Add the content to a directory.

	 *

	 * @author Adam Schmalhofer <Adam.Schmalhofer@gmx.de>

	 * @author A. Grandt

	 *

	 * @param String $realPath Path on the file system.

	 * @param String $zipPath  Filepath and name to be used in the archive.

	 * @param bool $zipPath    Add content recursively, default is TRUE.

	 */

	public function addDirectoryContent($realPath, $zipPath, $recursive = TRUE) {

		$iter = new DirectoryIterator($realPath);

		foreach ($iter as $file) {

			if ($file->isDot()) {

				continue;

			}

			$newRealPath = $file->getPathname();

			$newZipPath = self::pathJoin($zipPath, $file->getFilename());

			if ($file->isFile()) {

				$this->addLargeFile($newRealPath, $newZipPath);

			} else if ($recursive === TRUE) {

				$this->addDirectoryContent($newRealPath, $newZipPath, $recursive);

			}

		}

	}



	/**

	 * Add a file to the archive at the specified location and file name.

	 *

	 * @param String $dataFile    File name/path.

	 * @param String $filePath    Filepath and name to be used in the archive.

	 * @param int    $timestamp   (Optional) Timestamp for the added file, if omitted or set to 0, the current time will be used.

	 * @param String $fileComment (Optional) Comment to be added to the archive for this file. To use fileComment, timestamp must be given.

	 * @return bool $success

	 */

	public function addLargeFile($dataFile, $filePath, $timestamp = 0, $fileComment = NULL)   {

		if ($this->isFinalized) {

			return FALSE;

		}



		$this->openStream($filePath, $timestamp, $fileComment);



		$fh = fopen($dataFile, "rb");

		while(!feof($fh)) {

			$this->addStreamData(fread($fh, $this->streamChunkSize));

		}

		fclose($fh);



		$this->closeStream();



		return TRUE;

	}



	/**

	 * Create a stream to be used for large entries.

	 *

	 * @param String $filePath    Filepath and name to be used in the archive.

	 * @param int    $timestamp   (Optional) Timestamp for the added file, if omitted or set to 0, the current time will be used.

	 * @param String $fileComment (Optional) Comment to be added to the archive for this file. To use fileComment, timestamp must be given.

	 * @return bool $success

	 */

	public function openStream($filePath, $timestamp = 0, $fileComment = NULL)   {

		if ( !function_exists('sys_get_temp_dir')) {

			die ("ERROR: Zip " . self::VERSION . " requires PHP version 5.2.1 or above if large files are used.");

		}

		

		if ($this->isFinalized) {

			return FALSE;

		}



		if (is_null($this->zipFile)) {

			$this->zipFile = tmpfile();

			fwrite($this->zipFile, $this->zipData);

			$this->zipData = NULL;

		}



		if (strlen($this->streamFilePath) > 0) {

			closeStream();

		}

		$this->streamFile = tempnam(sys_get_temp_dir(), 'Zip');

		$this->streamData = gzopen($this->streamFile, "w9");

		$this->streamFilePath = $filePath;

		$this->streamTimestamp = $timestamp;

		$this->streamFileComment = $fileComment;

		$this->streamFileLength = 0;



		return TRUE;

	}



	/**

	 * Add data to the open stream.

	 *

	 * @param String $data

	 * @return $length bytes added or FALSE if the archive is finalized or there are no open stream.

	 */

	public function addStreamData($data) {

		if ($this->isFinalized || strlen($this->streamFilePath) == 0) {

			return FALSE;

		}



		$length = gzwrite($this->streamData, $data, strlen($data));

		if ($length != strlen($data)) {

			print "<p>Length mismatch</p>\n";

		}

		$this->streamFileLength += $length;



		return $length;

	}



	/**

	 * Close the current stream.

	 *

	 * @return bool $success

	 */

	public function closeStream() {

		if ($this->isFinalized || strlen($this->streamFilePath) == 0) {

			return FALSE;

		}



		fflush($this->streamData);

		gzclose($this->streamData);



		$gzType = "\x08\x00"; // Compression type 8 = deflate

		$gpFlags = "\x02\x00"; // General Purpose bit flags for compression type 8 it is: 0=Normal, 1=Maximum, 2=Fast, 3=super fast compression.



		$file_handle = fopen($this->streamFile, "rb");

		$stats = fstat($file_handle);

		$eof = $stats['size'];



		fseek($file_handle, $eof-8);

		$fileCRC32 = fread($file_handle, 4);

		$dataLength = $this->streamFileLength;//$gzl[1];



		$gzLength = $eof-10;

		$eof -= 9;



		fseek($file_handle, 10);



		$this->buildZipEntry($this->streamFilePath, $this->streamFileComment, $gpFlags, $gzType, $this->streamTimestamp, $fileCRC32, $gzLength, $dataLength, self::EXT_FILE_ATTR_FILE);

		while(!feof($file_handle)) {

			fwrite($this->zipFile, fread($file_handle, $this->streamChunkSize));

		}



		unlink($this->streamFile);

		$this->streamFile = NULL;

		$this->streamData = NULL;

		$this->streamFilePath = NULL;

		$this->streamTimestamp = NULL;

		$this->streamFileComment = NULL;

		$this->streamFileLength = 0;



		return TRUE;

	}



	/**

	 * Close the archive.

	 * A closed archive can no longer have new files added to it.

	 *

	 * @return bool $success

	 */

	public function finalize() {

		if(!$this->isFinalized) {

			if (strlen($this->streamFilePath) > 0) {

				$this->closeStream();

			}

			$cd = implode("", $this->cdRec);

				

			$cdRec = $cd . self::ZIP_END_OF_CENTRAL_DIRECTORY

			. pack("v", sizeof($this->cdRec))

			. pack("v", sizeof($this->cdRec))

			. pack("V", strlen($cd))

			. pack("V", $this->offset);

			if (!is_null($this->zipComment)) {

				$cdRec .= pack("v", strlen($this->zipComment)) . $this->zipComment;

			} else {

				$cdRec .= "\x00\x00";

			}

				

			if (is_null($this->zipFile)) {

				$this->zipData .= $cdRec;

			} else {

				fwrite($this->zipFile, $cdRec);

				fflush($this->zipFile);

			}

			$this->isFinalized = TRUE;

			$cd = NULL;

			$this->cdRec = NULL;

				

			return TRUE;

		}

		return FALSE;

	}



	/**

	 * Get the handle ressource for the archive zip file.

	 * If the zip haven't been finalized yet, this will cause it to become finalized

	 *

	 * @return zip file handle

	 */

	public function getZipFile() {

		if(!$this->isFinalized) {

			$this->finalize();

		}

		if (is_null($this->zipFile)) {

			$this->zipFile = tmpfile();

			fwrite($this->zipFile, $this->zipData);

			$this->zipData = NULL;

		}

		rewind($this->zipFile);



		return $this->zipFile;

	}



	/**

	 * Get the zip file contents

	 * If the zip haven't been finalized yet, this will cause it to become finalized

	 *

	 * @return zip data

	 */

	public function getZipData( $return_file) {

		if(!$this->isFinalized) {

			$this->finalize();

		}

		if (is_null($this->zipFile)) {

			return $this->zipData;

		} else {

			rewind($this->zipFile);

			$filestat = fstat($this->zipFile);
			  header('Pragma: public');
                header("Last-Modified: " . gmdate("D, d M Y H:i:s T"));
                header("Expires: 0");
                header("Accept-Ranges: bytes");
                //header("Connection: Keep-Alive");
                header("Content-Type: application/zip");
                header('Content-Disposition: attachment; filename="' .  $return_file . '";');
                header("Content-Transfer-Encoding: binary");
                flush();
				echo  fread($this->zipFile, $filestat['size']);

		}

	}



	/**

	 * Send the archive as a zip download

	 *

	 * @param String $fileName The name of the Zip archive, ie. "archive.zip".

	 * @param String $contentType Content mime type. Optional, defaults to "application/zip".

	 * @return bool $success

	 */

	function sendZip($fileName, $contentType = "application/zip") {

		if(!$this->isFinalized) {

			$this->finalize();

		}



		if (!headers_sent($headerFile, $headerLine) or die("<p><strong>Error:</strong> Unable to send file $fileName. HTML Headers have already been sent from <strong>$headerFile</strong> in line <strong>$headerLine</strong></p>")) {

			if ((ob_get_contents() === FALSE || ob_get_contents() == '') or die("\n<p><strong>Error:</strong> Unable to send file <strong>$fileName.epub</strong>. Output buffer contains the following text (typically warnings or errors):<br>" . ob_get_contents() . "</p>")) {

				if (ini_get('zlib.output_compression')) {

					ini_set('zlib.output_compression', 'Off');

				}



				header("Pragma: public");

				header("Last-Modified: " . gmdate("D, d M Y H:i:s T"));

				header("Expires: 0");

				header("Accept-Ranges: bytes");

				header("Connection: close");

				header("Content-Type: " . $contentType);

				header('Content-Disposition: attachment; filename="' . $fileName . '";' );

				header("Content-Transfer-Encoding: binary");

				header("Content-Length: ". $this->getArchiveSize());



				if (is_null($this->zipFile)) {

					echo $this->zipData;

				} else {

					rewind($this->zipFile);



					while(!feof($this->zipFile)) {

						echo fread($this->zipFile, $this->streamChunkSize);

					}

				}

			}

			return TRUE;

		}

		return FALSE;

	}



	/**

	 * Return the current size of the archive

	 *

	 * @return $size Size of the archive

	 */

	public function getArchiveSize() {

		if (is_null($this->zipFile)) {

			return strlen($this->zipData);

		}

		$filestat = fstat($this->zipFile);



		return $filestat['size'];

	}



	/**

	 * Calculate the 2 byte dostime used in the zip entries.

	 *

	 * @param int $timestamp

	 * @return 2-byte encoded DOS Date

	 */

	private function getDosTime($timestamp = 0) {

		$timestamp = (int)$timestamp;

		$date = ($timestamp == 0 ? getdate() : getDate($timestamp));

		if ($date["year"] >= 1980) {

			return pack("V", (($date["mday"] + ($date["mon"] << 5) + (($date["year"]-1980) << 9)) << 16) |

			(($date["seconds"] >> 1) + ($date["minutes"] << 5) + ($date["hours"] << 11)));

		}

		return "\x00\x00\x00\x00";

	}



	/**

	 * Build the Zip file structures

	 *

	 * @param String $filePath

	 * @param String $fileComment

	 * @param String $gpFlags

	 * @param String $gzType

	 * @param int $timestamp

	 * @param string $fileCRC32

	 * @param int $gzLength

	 * @param int $dataLength

	 * @param integer $extFileAttr Use self::EXT_FILE_ATTR_FILE for files, self::EXT_FILE_ATTR_DIR for Directories.

	 */

	private function buildZipEntry($filePath, $fileComment, $gpFlags, $gzType, $timestamp, $fileCRC32, $gzLength, $dataLength, $extFileAttr) {

		$filePath = str_replace("\\", "/", $filePath);

		$fileCommentLength = (is_null($fileComment) ? 0 : strlen($fileComment));



		$timestamp = (int)$timestamp;

		$timestamp = ($timestamp == 0 ? time() : $timestamp);

		

		$dosTime = $this->getDosTime($timestamp);



		$zipEntry  = self::ZIP_LOCAL_FILE_HEADER;

		$zipEntry .= self::ATTR_VERSION_TO_EXTRACT;

		$zipEntry .= $gpFlags . $gzType . $dosTime. $fileCRC32;

		$zipEntry .= pack("VV", $gzLength, $dataLength);

		$zipEntry .= pack("v", strlen($filePath) ); // File name length

		$zipEntry .= "\x10\x00"; // Extra field length

		$zipEntry .= $filePath; // FileName 

		// Extra fields

		$zipEntry .= "\x55\x58"; 			// 0x5855	Short	tag for this extra block type ("UX")

		$zipEntry .= "\x0c\x00";   			// TSize	Short	total data size for this block

		$zipEntry .= pack("V", $timestamp);	// AcTime	Long	time of last access (UTC/GMT)

		$zipEntry .= pack("V", $timestamp);	// ModTime	Long	time of last modification (UTC/GMT)

		$zipEntry .= "\x00\x00\x00\x00";



		if (is_null($this->zipFile)) {

			$this->zipData .= $zipEntry;

		} else {

			fwrite($this->zipFile, $zipEntry);

		}



		$cdEntry  = self::ZIP_CENTRAL_FILE_HEADER;

		$cdEntry .= self::ATTR_MADE_BY_VERSION;

		$cdEntry .= self::ATTR_VERSION_TO_EXTRACT;

		$cdEntry .= $gpFlags . $gzType . $dosTime. $fileCRC32;

		$cdEntry .= pack("VV", $gzLength, $dataLength);

		$cdEntry .= pack("v", strlen($filePath)); // Filename length

		$cdEntry .= "\x0c\x00"; // Extra field length

		$cdEntry .= pack("v", $fileCommentLength); // File comment length

		$cdEntry .= "\x00\x00"; // Disk number start

		$cdEntry .= "\x00\x00"; // internal file attributes

		$cdEntry .= $extFileAttr; // External file attributes

		$cdEntry .= pack("V", $this->offset ); // Relative offset of local header

		$cdEntry .= $filePath; // FileName 

		// Extra fields

		$cdEntry .= "\x55\x58"; 			// 0x5855	Short	tag for this extra block type ("UX")

		$cdEntry .= "\x08\x00";   			// TSize	Short	total data size for this block

		$cdEntry .= pack("V", $timestamp);	// AcTime	Long	time of last access (UTC/GMT)

		$cdEntry .= pack("V", $timestamp);	// ModTime	Long	time of last modification (UTC/GMT)



		if (!is_null($fileComment)) {

			$cdEntry .= $fileComment; // Comment

		}



		$this->cdRec[] = $cdEntry;

		$this->offset += strlen($zipEntry) + $gzLength;

	}



	/**

	 * Join $file to $dir path, and clean up any excess slashes.

	 *

	 * @param String $dir

	 * @param String $file

	 */

	public static function pathJoin($dir, $file) {

		if (empty($dir) || empty($file)) {

			return self::getRelativePath($dir . $file);

		}

		return self::getRelativePath($dir . '/' . $file);

	}



	/**

	 * Clean up a path, removing any unnecessary elements such as /./, // or redundant ../ segments.

	 * If the path starts with a "/", it is deemed an absolute path and any /../ in the beginning is stripped off.

	 * The returned path will not end in a "/".

	 *

	 * @param String $relPath The path to clean up

	 * @return String the clean path

	 */

	public static function getRelativePath($path) {

		$path = preg_replace("#/+\.?/+#", "/", str_replace("\\", "/", $path));

		$dirs = explode("/", rtrim(preg_replace('#^(\./)+#', '', $path), '/'));

				

		$offset = 0;

		$sub = 0;

		$subOffset = 0;

		$root = "";



		if (empty($dirs[0])) {

			$root = "/";

			$dirs = array_splice($dirs, 1);

		} else if (preg_match("#[A-Za-z]:#", $dirs[0])) {

			$root = strtoupper($dirs[0]) . "/";

			$dirs = array_splice($dirs, 1);

		} 



		$newDirs = array();

		foreach($dirs as $dir) {

			if ($dir !== "..") {

				$subOffset--;	

				$newDirs[++$offset] = $dir;

			} else {

				$subOffset++;

				if (--$offset < 0) {

					$offset = 0;

					if ($subOffset > $sub) {

						$sub++;

					} 

				}

			}

		}



		if (empty($root)) {

			$root = str_repeat("../", $sub);

		} 

		return $root . implode("/", array_slice($newDirs, 0, $offset));

	}

}

?>