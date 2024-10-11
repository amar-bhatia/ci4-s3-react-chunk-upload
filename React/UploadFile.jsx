
import Uppy from "@uppy/core";
import Tus from "@uppy/tus";
import { Dashboard } from "@uppy/react";
import "@uppy/core/dist/style.min.css";
import "@uppy/dashboard/dist/style.min.css";

// Access the base URL
const baseUrl = import.meta.env.VITE_BACKEND_DS_URL;

const UploadFileModal = () => {
  // Initialize Uppy
  const uppy = new Uppy({
    autoProceed: false, // Automatically start uploads after adding files
  }).use(Tus, {
    endpoint: `${baseUrl}/upload-file`,
    withCredentials: true, // Ensure cookies (JWT) are sent with the request
    headers: {
      Domain: window.location.hostname,
    },
    chunkSize: 5242880, // 5MB chunks
    retryDelays: [0, 1000, 3000, 5000],
  });

  // Event listener to add metadata before upload
  uppy.on('file-added', (file) => {
    // Check if file.size is defined
    if (file.size !== null && file.size !== undefined) {
      const totalChunks = Math.ceil(file.size / 5242880); // Example chunk size of 5MB

      // Set metadata on the file
      uppy.setFileMeta(file.id, {
        name: file.name,
        type: file.type,
        totalChunks: totalChunks,
        fileSize: file.size, // Add the file size to the metadata
        fileType: fileType,
      });
    } else {
      console.error("File size is not available.");
    }
  });

  // Add an event listener for chunk upload progress
  uppy.on('upload', (file) => {
    console.log('File object:', file);
  });

  uppy.on('complete', (result) => {
    console.log('Upload complete! We’ve uploaded these files:', result.successful);

    if (result && result.successful && result.successful.length > 0) {
      result.successful.forEach((file) => {
        const uploadURL = file.id;

        if (uploadURL) {
          const tusKeys = Object.keys(localStorage).filter(key => key.startsWith("tus::") && key.includes(uploadURL));
          
          tusKeys.forEach(key => {
            localStorage.removeItem(key);
          });
        }
      });

      // Optionally cancel all uploads and reset Uppy’s state
      uppy.cancelAll(); // This will cancel any ongoing uploads and reset Uppy’s state
    }
    
  });

  uppy.on('error', (error) => {
    console.error('Upload error:', error);
  });

  return (
    <div className="card flex justify-content-center">
        <Dashboard uppy={uppy} />
      
    </div>
  );
};

export default UploadFileModal;
