<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\respondTrait;
use App\Libraries\S3_bucket;


class S3_controller extends ResourceController
{

    function __construct()
    {
        $this->S3_model = new \App\Models\S3_model();
        $this->s3_bucket = new S3_bucket();
    }

    public function uploadFile($upload_id = ''){

        // Retrieve the uploaded chunk from the request body
        $chunk_data = 'php://input';

        if (!fopen($chunk_data, 'r')) {
            return $this->response->setStatusCode(400)->setBody('No chunk data found.');
        }

        switch ($_SERVER['REQUEST_METHOD']) {
            case 'OPTIONS':
                exit;
                break;
            case 'POST':
                $this->chunkUploadPostRequest();
                break;
            case 'PATCH':
                $this->chunkUploadPatchRequest($upload_id,$chunk_data);
                break;
            case 'HEAD':
                $this->chunkUploadHeadRequest($upload_id);
                break;
            case 'DELETE':
                $this->chunkUploadDeleteRequest($upload_id);
                break;
            default:
                // code...
                break;
        }
    }

    private function chunkUploadPostRequest(){
        // Get the 'Upload-Metadata' header
        $upload_metadata = $this->request->getHeaderLine('Upload-Metadata');
        
        // Decode the upload-metadata header
        $metadata = [];
        $pairs = explode(',', $upload_metadata);
        foreach ($pairs as $pair) {
            list($key, $value) = explode(' ', trim($pair), 2);
            $metadata[$key] = base64_decode($value);
        }

        // Extracting file name and type from metadata
        $file_data = [
            'file_name' => $metadata['name'],
            'file_path' => 'chunk-upload-test/'.$file_name,
            'file_type' => $metadata['type'],
            'total_chunks' => $metadata['totalChunks'],
            'total_file_size' => $metadata['fileSize']
        ];

        $upload_id = $this->s3_bucket->generateUploadID($file_data);

        $location = "upload-file/".$upload_id;

        // Set the Location header

        return $this->respond(['message' => 'Upload initiated', 'uploadId' => $upload_id])
                    ->setStatusCode(201)
                    ->setHeader('Location', $location)
                    ->setHeader('Tus-Resumable', '4.*')  // Must be included per Tus protocol
                    ->setHeader('Access-Control-Expose-Headers', 'Location'); // Allow Location header to be exposed for CORS
    }

    private function chunkUploadPatchRequest($upload_id,$chunk_data){
        $file_data = $this->S3_model->fetchChunkUploadData($upload_id);
        $prev_chunk_data = $this->S3_model->fetchUploadedChunkFileData($file_data['ChunkUploadTrackingID']);
        $chunk_index = (!empty($prev_chunk_data['uploaded_chunks']))?$prev_chunk_data['uploaded_chunks'] + 1:1;

        $current_offset = $_SERVER['HTTP_UPLOAD_OFFSET']; // Get the current offset from the client
        $chunk_size = $_SERVER['CONTENT_LENGTH']; // Get the size of the current chunk

        $chunk_upload_files_data = [
            'upload_id' => $upload_id,
            'file_chunk' => $chunk_data,
            'file_name' => $file_data['FileName'],
            'part_number' => $chunk_index,
            'total_chunks' => $file_data['TotalChunks'],
            'chunk_size' => $chunk_size
        ];

        $chunk_file_data = $this->s3_bucket->chunkUpload($file_data['ChunkUploadTrackingID'], $chunk_upload_files_data);

        $location = "upload-file/".$upload_id;

        return $this->respond([
            'message' => 'Chunk Uploaded', 
            'uploadId' => $upload_id,
            'data' => $chunk_file_data
        ])
        ->setStatusCode(204)
        ->setHeader('Upload-Offset',($current_offset + $chunk_size))
        ->setHeader('Location', $location)
        ->setHeader('Tus-Resumable', '4.*')  // Must be included per Tus protocol
        ->setHeader('Access-Control-Expose-Headers', 'Location'); // Allow Location header to be exposed for CORS;
    }

    private function chunkUploadHeadRequest($upload_id){
        // Fetch the current file metadata from the database
        $file_data = $this->S3_model->fetchChunkUploadData($upload_id);
        $chunk_data = $this->S3_model->fetchUploadedChunkFileData($file_data['ChunkUploadTrackingID']);

        if (!$file_data) {
            return $this->failNotFound('Upload not found.');
        }

        // Calculate the total uploaded size, assuming you track uploaded bytes
        $current_offset =  (!empty($chunk_data['ChunkSize']))?$chunk_data['ChunkSize']:0; // Get total uploaded bytes

        if(!empty($chunk_data) && $chunk_data['ChunkSize'] == $file_data['TotalFileSize']){
            $this->S3_model->deleteChunkUploadData($upload_id);
        }

        // Return the current offset and total file length
        return $this->response
                    ->setStatusCode(200)
                    ->setHeader('Upload-Offset', (string)$current_offset)  // The current upload offset
                    ->setHeader('Upload-Length', (string)$file_data['TotalFileSize'])   // The total file length
                    ->setHeader('Tus-Resumable', '1.0.0')
                    ->setHeader('Upload-Metadata', 'name ' . base64_encode($file_data['FileName']) . ', type ' . base64_encode($file_data['FileType']))
                    ->setHeader('Access-Control-Expose-Headers', 'Upload-Offset, Upload-Length');
    }

    private function chunkUploadDeleteRequest($upload_id){
        
        $this->S3_model->deleteChunkUploadData($upload_id);
        
        return $this->response
                    ->setStatusCode(204)
                    ->setHeader('Tus-Resumable', '1.0.0');
    }
}
