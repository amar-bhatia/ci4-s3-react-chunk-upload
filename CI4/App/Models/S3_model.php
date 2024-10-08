<?php

namespace App\Models;

use CodeIgniter\Model;

class S3_model extends Model
{
    function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    public function saveChunkUploadTrackingData($chunk_upload_tracking_data){
        $this->db->table('chunk_upload_tracking')
                          ->insert($chunk_upload_tracking_data);
    }

    public function saveChunkUploadFileData($chunk_upload_file_data){
        $this->db->table('chunk_upload_files')
                          ->insert($chunk_upload_file_data);
    }

    public function fetchChunkUploadedFiles($upload_tracking_id){
        $query = $this->db->table('chunk_upload_files')
                                   ->select('ETag,PartNumber')
                                   ->orderBy('PartNumber','ASC')
                                   ->getWhere(['ChunkUploadTrackingID' => $upload_tracking_id])
                                   ->getResultArray();

        return $query;
    }
  
    public function deleteChunkUploadData($upload_id){
        $this->db->table('chunk_upload_tracking')
                          ->delete(['UploadID' => $upload_id]);
    }

    public function fetchUploadedChunkFileData($upload_tracking_id){
        $query = $this->db->table(CHUNK_UPLOAD_FILES)
                                   ->select('SUM(ChunkSize) as ChunkSize,COUNT(DISTINCT(ChunkUploadFileID)) as uploaded_chunks')
                                   ->getWhere(['ChunkUploadTrackingID' => $upload_tracking_id])
                                   ->getRowArray();
        return $query;
    }

    public function fetchChunkUploadData($upload_id){
        $query = $this->db->table(CHUNK_UPLOAD_TRACKING)
                                   ->select('ChunkUploadTrackingID,FileName,FileType,TotalChunks,TotalFileSize')
                                   ->groupBy('UploadID')
                                   ->getWhere(['UploadID' => $upload_id])
                                   ->getRowArray();

        return $query;
    }
}
