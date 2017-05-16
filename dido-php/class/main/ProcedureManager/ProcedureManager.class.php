<?php

class ProcedureManager implements IProcedureManager {

	private $_dbConnector;

	private $_MDPManager;

	private $_DPManager;

	private $_FTPDataSource;
	
	const OPEN = 0;
	
	const CLOSED = 1;
	
	const INCOMPLETE = - 1;

	public function __construct(IDBConnector $dbConnector, IFTPDataSource $ftpDataSource) {
		$this->_dbConnector = $dbConnector;
		$this->_MDPManager = new MasterDocumentProcedureManager ( $dbConnector );
		$this->_DPManager = new DocumentProcedureManager ( $dbConnector );
		$this->_FTPDataSource = $ftpDataSource;
	}

	public function createMasterdocument($md, $md_data) {

		// Creo il MD;
		$this->_dbConnector->begin ();

		$md [Masterdocument::FTP_FOLDER] = $this->_FTPDataSource->getNewPathFromXml ( $md [Masterdocument::XML] );
		$new_md = $this->_MDPManager->create ( $md, $md_data );
		if (! $new_md) {
			
			$this->removeMasterdocumentFolder( $md [Masterdocument::FTP_FOLDER] );
			$this->_dbConnector->rollback ();
			return false;
		}
		// Creo cartella ftp
		
		if (! $this->_FTPDataSource->createFolder ( $new_md [Masterdocument::FTP_FOLDER] 
				. $this->_FTPDataSource-> getFolderNameFromMasterdocument($new_md))) {
			return false;
		}

		$this->_dbConnector->commit ();
		return $new_md;
	}

	public function updateMasterdocument($md_data) {
		return $this->_MDPManager->update ( $md_data );
	}

	public function closeIncompleteMasterdocument($md) {
		return $this->_MDPManager->close($md, self::INCOMPLETE );
	}
	
	public function deleteMasterdocument($md) {
		// Elimino il MD dal DB
		
		$this->_dbConnector->begin ();
		
		if(!$this->_MDPManager->delete ( $md)){
			$this->_dbConnector->rollback ();
			return false;
		}
		// Elimino la folder del MasterDocument dall'FTP
		if (! $this->_FTPDataSource->deleteFolderRecursively($md [Masterdocument::FTP_FOLDER]
				. $this->_FTPDataSource-> getFolderNameFromMasterdocument($md))) {
			$this->_dbConnector->rollback ();
			return false;
		}
		$this->_dbConnector->commit();
		return true;
	}

	public function removeMasterdocumentFolder($repositoryPath) {
		$this->_FTPDataSource->deleteFolder ( $repositoryPath );
	}

	public function createDocument($doc, $data, $filePath, $repositoryPath) {
		$this->_dbConnector->begin ();
		$new_doc = $this->_DPManager->create ( $doc, $data );
		if (! $new_doc) {
			$this->_dbConnector->rollback ();
			return false;
		}
		
		if (! $this->uploadFile( $new_doc, $filePath, $repositoryPath )) {
			$this->_dbConnector->rollback ();
			return false;
		}
		
		$this->_dbConnector->commit ();
		return $new_doc;
	}

	public function updateDocument($document, $data, $filePath = null, $repositoryPath = null) {
		$this->_dbConnector->begin ();
		if (! $this->_DPManager->update ( $data )) {
			$this->_dbConnector->rollback ();
			return false;
		}
		
		if (! is_null ( $filePath ) && ! is_null ( $repositoryPath )) {
			if (! $this->uploadFile ( $document, $filePath, $repositoryPath )) {
				$this->_dbConnector->rollback ();
				return false;
			}
		}
		
		$this->_dbConnector->commit ();
		return $new_doc;
	}

	public function deleteDocument($doc, $ftpFolder) {
		$this->_dbConnector->begin ();
		if (! $this->_DPManager->delete ( $doc, $ftpFolder )) {
			$this->_dbConnector->rollback ();
			return false;
		}
		$filePath = $ftpFolder . $this->_FTPDataSource->getFilenameFromDocument ( $doc );
		if (! $this->_FTPDataSource->deleteFile ( $filePath )) {
			$this->_dbConnector->rollback ();
			return false;
		}
		$this->_dbConnector->commit ();
		return true;
	}

	private function uploadFile($doc, $filePath, $repositoryPath) {
		$filename = $repositoryPath . $this->_FTPDataSource->getFilenameFromDocument ( $doc );
		if (! $this->_FTPDataSource->upload ( $filePath, $filename )) {		
			return false;
		}
		return true;
	}
}
?>