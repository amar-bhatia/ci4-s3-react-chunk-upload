<?php 
    namespace App\Libraries;

    use Aws\S3\S3Client;
    use Aws\S3\Exception\S3Exception as S3;

    class S3_bucket{

        function __construct()
        {
            
            $this->default_bucket = env('S3_DEFAULT_BUCKET');

            $this->s3_client = new S3Client([
                'region' => 'ap-south-1',
                'version' => 'latest',
                'credentials' => [
                    'key' => env('S3_KEY'),
                    'secret' => env('S3_SECRET')
                ]
            ]);
        }

        public function generateUploadID($file_data, $bucket = ''){
            $bucket = (!empty($bucket))?$bucket:$this->default_bucket;

            $file_name = $file_data['file_name'];
            $file_path = $file_data['file_path'];
            $file_type = $file_data['file_type'];
            $total_chunks = $file_data['total_chunks'];
            $total_file_size = $file_data['total_file_size'];
            
            $createResult = $this->s3_client->createMultipartUpload([
                'Bucket' => $bucket,
                'Key'    => $file_path, // Unique file name in S3
            ]);

            $s3_tracking_model = new \Models\S3_model();
            $registered_user_id = $this->jwt_details['data']['RegisteredUserID'];

            // Store the part number and ETag
            $chunk_upload_tracking_data = [
                'UploadID' => $createResult['UploadId'],
                'FileName' => $file_name,
                'FileType' => $file_type,
                'TotalChunks' => $total_chunks,
                'TotalFileSize' => $total_file_size
            ];

            $s3_tracking_model->saveChunkUploadTrackingData($chunk_upload_tracking_data);


            return $createResult['UploadId'];
        }

        public function chunkUpload($upload_tracking_id, $chunk_upload_files_data, $bucket = '', $folder = '', $file_custom_name = '', $file_access_type = 'public-read'){

           /* ------------------------------ 
            Following are the File Access Types

            1) private
            2) public-read (Default)
            3) public-read-write
            4) authenticated-read
            5) bucket-owner-read
            6) bucket-owner-full-control
            7) log-delivery-write
            ---------------------------------- */
          
            $bucket = (!empty($bucket))?$bucket:$this->default_bucket;

            $file_name = $chunk_upload_files_data['file_name'];
            $file_chunk = $chunk_upload_files_data['file_chunk'];
            $upload_id = $chunk_upload_files_data['upload_id'];
            $part_number = $chunk_upload_files_data['part_number'];
            $total_chunks = $chunk_upload_files_data['total_chunks'];

            try {

                $registered_user_id = $this->jwt_details['data']['RegisteredUserID'];

                // Upload the chunk (part) to S3
                $upload_part_result  = $this->s3_client->uploadPart([
                    'Bucket'     => $bucket,
                    'Key'        => 'chunk-upload-test/'.$file_name,
                    'UploadId'   => $upload_id,
                    'PartNumber' => $part_number,
                    'Body'       => fopen($file_chunk, 'rb'),
                ]);

                // Get ETag for each part
                $etag = $upload_part_result['ETag'];

                $s3_tracking_model = new \App\Models\S3_model();

                // Store the part number and ETag
                $chunk_upload_file_data = [
                    'ChunkUploadTrackingID' => $upload_tracking_id,
                    'ETag'     => $etag,
                    'PartNumber' => $part_number,
                    'ChunkSize' => $chunk_upload_files_data['chunk_size'],
                ];

                $s3_tracking_model->saveChunkUploadFileData($chunk_upload_file_data);

                if($part_number == $total_chunks){
                    
                    $stored_upload_tracking_data = $collateral_model->fetchChunkUploadedFiles($upload_tracking_id);

                    for ($i=0; $i <count($stored_upload_tracking_data) ; $i++) { 
                        $partsArray[] = [
                            'ETag'       => $stored_upload_tracking_data[$i]['ETag'],
                            'PartNumber' => $stored_upload_tracking_data[$i]['PartNumber'],
                        ];   
                    }

                    // Step 3: After all parts are uploaded, complete the multipart upload
                    $completeResult = $this->s3_client->completeMultipartUpload([
                        'Bucket'   => $bucket,
                        'Key'      => 'chunk-upload-test/'.$file_name,
                        'UploadId' => $upload_id,
                        'MultipartUpload' => [
                            'Parts' => $partsArray, // ETag and part number for each part
                        ],
                    ]);

                    return [
                        'status' => true,
                        'file_path' => $completeResult['Key']
                    ];
                }else{
                    return [
                        'status' => true,
                        'UploadID' => $upload_id
                    ];
                }

            } catch (Aws\Exception\AwsException $e) {
                // Handle errors
                $result = ['status' => false, 'message' => $e->getMessage()];

                return $result;
            }
        }
    }
