const express = require("express");
const multer = require("multer");
const path = require("path");
const cors = require("cors");
const fs = require("fs");

const app = express();

// Allow CORS for external access (modify "origin" if needed)
const corsOptions = {
  origin: "*", // Change "*" to your frontend URL if needed
  methods: "GET,POST",
  allowedHeaders: "Content-Type"
};
app.use(cors(corsOptions));

app.use(express.static("uploads"));

// Function to generate timestamped folder name using local system time
const getUploadFolder = () => {
  const now = new Date();
  const localTime = now.toLocaleString('en-GB', { timeZoneName: 'short' }).replace(/[^0-9]/g, '-');
  return path.join("uploads", localTime);
};

// Configure Multer storage
const storage = multer.diskStorage({
  destination: (req, file, cb) => {
    const uploadFolder = getUploadFolder();

    // Ensure folder exists with correct permissions
    if (!fs.existsSync(uploadFolder)) {
      fs.mkdirSync(uploadFolder, { recursive: true });
      fs.chmodSync(uploadFolder, 0o777); // Make sure the folder is writable
    }

    cb(null, uploadFolder);
  },
  filename: (req, file, cb) => {
    const extname = path.extname(file.originalname).toLowerCase();
    
    // Handle both .nii and .nii.gz correctly
    if (extname === '.gz' && file.originalname.endsWith('.nii.gz')) {
      cb(null, Date.now() + '.nii.gz');
    } else {
      cb(null, Date.now() + extname);
    }
  }
});

const upload = multer({ storage });

// Handle file uploads
app.post("/upload", upload.single("file"), (req, res) => {
  if (!req.file) {
    return res.status(400).json({ error: "No file uploaded" });
  }
  res.json({
    message: "File uploaded successfully",
    filePath: req.file.path
  });
});

// Allow external connections (change port if necessary)
const PORT = 3001;
app.listen(PORT, "0.0.0.0", () => console.log(`Server running on http://0.0.0.0:${PORT}`));
