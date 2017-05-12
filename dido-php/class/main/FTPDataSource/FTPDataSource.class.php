<?php

class FTPDataSource implements IFTPDataSource{

	private $_ftpConnector;

	public function __construct(IFTPConnector $ftpConnector = null) {
		$this->_ftpConnector = is_null ( $ftpConnector ) ? new FTPConnector() : $ftpConnector;
	}

	public function createFolder($folder) {
		return $this->_ftpConnector->mksubdirs ( $folder );
	}

	public function deleteFolder($folder) {
		return $this->_ftpConnector->deleteFolder ( $folder );
	}

	public function getNewPathFromXml($xml) {
		return dirname ( $xml ) . DIRECTORY_SEPARATOR . date ( "Y" ) . DIRECTORY_SEPARATOR . date ( "m" ) . DIRECTORY_SEPARATOR;
	}

	public function getFilenameFromDocument($document) {
		return FormHelper::fieldFromLabel ( $document [Document::NOME] . " " . $document [Document::ID_DOC] . "." . $document [Document::EXTENSION] );
	}

	public function deleteFile($filePath) {
		return $this->_ftpConnector->delete ( $filePath );
	}
	
	public function getTempFile($file, $tmpPath = FILES_PATH) {
		return $this->_ftpConnector->getTempFile($file, $tmpPath);
	}
	
	public function upload($file, $tmpPath = FILES_PATH) {
		$result = $this->_ftpConnector->upload($file, $tmpPath);
		unlink($file);
		return $result;
	}
	
}

?>