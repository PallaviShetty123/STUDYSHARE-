# StudyShare - Smart Study Material Sharing Platform

A complete web-based application for sharing study materials with secure authentication, filtering by department and semester, and an admin panel for managing content.

## 🎯 Project Overview

StudyShare is a platform where students can access study materials based on their academic details (Department & Semester). The system supports two roles:

- **Students**: Can browse, like, and download notes relevant to their department and semester
- **Admins**: Can upload notes, manage content, and upload student datasets

## 📁 Project Structure

```
studyshare/
├── index.php                    # Landing page
├── logout.php                   # Logout handler
├── admin/
│   ├── login.php               # Admin login
│   ├── dashboard.php           # Admin dashboard
│   ├── upload_notes.php        # Upload notes form
│   ├── manage_notes.php        # View all notes
│   ├── edit_note.php           # Edit note
│   ├── delete_note.php         # Delete note handler
│   ├── manage_students.php     # Import students CSV
│   └── delete_student.php      # Delete student
├── user/
│   ├── login.php               # Student login
│   ├── dashboard.php           # Student dashboard
│   ├── notes.php               # Browse notes
│   ├── profile.php             # Student profile
│   ├── download.php            # Download handler
│   └── like.php                # Like/unlike handler
├── common/
│   ├── config.php              # Configuration
│   ├── db.php                  # Database connection
│   ├── functions.php           # Helper functions
│   ├── auth_user.php           # Student auth check
│   └── auth_admin.php          # Admin auth check
├── database/
│   └── studyshare.sql          # Database schema
├── uploads/
│   ├── notes/                  # Uploaded note files
│   └── profile/                # Student profile pictures
├── assets/
│   ├── css/
│   │   ├── style.css           # Main styles
│   │   ├── login.css           # Login page styles
│   │   ├── dashboard.css       # Dashboard styles
│   │   ├── notes.css           # Notes page styles
│   │   └── profile.css         # Profile page styles
│   └── js/
│       └── script.js           # JavaScript functionality
└── README.md                    # This file
```

## 🗄️ Database Schema

### Admin Table
```sql
username (VARCHAR)
password (VARCHAR) - MD5 hashed
```

### Students Table
```sql
roll_no (Primary Key)
name
dob (Date of Birth)
department
semester
profile_image
created_at
```

### Notes Table
```sql
id (Primary Key)
subject
description
file_path
department
semester
upload_date
likes (count)
uploaded_by
```

### Likes Table
```sql
id
roll_no (Foreign Key to students)
note_id (Foreign Key to notes)
created_at
UNIQUE(roll_no, note_id) - Prevent duplicate likes
```

### Downloads Table (Optional)
```sql
id
roll_no
note_id
download_date
```

## 🚀 Installation & Setup

### Prerequisites
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web server (Apache, Nginx, etc.)

### Step 1: Database Setup

1. Open phpMyAdmin or MySQL console
2. Execute the SQL file:
   ```bash
   mysql -u root -p < database/studyshare.sql
   ```
3. Default admin credentials:
   - Username: `admin`
   - Password: `admin123`

### Step 2: Configuration

Create a `.env` file in the project root (optional):
```env
DB_HOST=localhost
DB_NAME=studyshare
DB_USER=root
DB_PASS=
```

Or edit `common/config.php` directly with your database credentials.

### Step 3: Directory Permissions

Ensure these directories are writable:
```bash
chmod 755 uploads/
chmod 755 uploads/notes/
chmod 755 uploads/profile/
```

### Step 4: Start the Server

If using PHP's built-in server:
```bash
php -S localhost:8000
```

Then visit: `http://localhost:8000`

## 👨‍🎓 Student Module

### Student Login
- **Username**: Roll Number (e.g., CS001)
- **Password**: Date of Birth (YYYY-MM-DD format)

### Available Features

1. **Dashboard**: 
   - View personal information (Roll No, Department, Semester)
   - See available subjects as cards
   - Browse recent notes

2. **Browse Notes**:
   - Filter notes by subject
   - Like/unlike notes
   - Download notes as documents

3. **Profile**:
   - View student information
   - Upload profile picture (JPG/PNG, max 5MB)
   - View member since date

## 👨‍💼 Admin Module

### Admin Login
- **Username**: `admin` (default)
- **Password**: `admin123` (default)

### Available Features

1. **Dashboard**:
   - View statistics (Total Students, Notes, Likes)
   - Browse recent uploaded notes
   - Quick access to all management features

2. **Upload Notes**:
   - Select Department (CSE, ECE, ME, CE)
   - Select Semester (1-8)
   - Upload PDF or DOC files (max 10MB)
   - Add description for each note

3. **Manage Notes**:
   - View all uploaded notes
   - Edit note details
   - Delete notes and associated files

4. **Manage Students**:
   - Import student dataset from CSV
   - View all students
   - Delete students

### CSV Import Format

Create a CSV file with the following format:
```csv
roll_no,name,dob,department,semester
CS001,John Doe,2003-05-15,CSE,4
CS002,Jane Smith,2003-08-22,CSE,4
EC001,Mike Wilson,2003-03-10,ECE,4
```

## 🔐 Security Features

1. **Session-Based Authentication**: Secure login using PHP sessions
2. **Input Sanitization**: All user inputs are sanitized and validated
3. **File Validation**: Only PDF and DOC files allowed for notes
4. **Role-Based Access**: Separate authentication for students and admins
5. **Like Deduplication**: Unique constraint prevents duplicate likes
6. **Profile Picture Validation**: Image type and size validation

## 🎨 UI Features

- **Modern Clean Design**: Professional and user-friendly interface
- **Responsive Layout**: Works on desktop, tablet, and mobile devices
- **Gradient Sidebar**: Beautiful color scheme with active states
- **Interactive Cards**: Hovering effects on subject and note cards
- **Toast Notifications**: User feedback for actions like/unlike
- **Smooth Animations**: Transitions and micro-interactions

## 📊 Data Filtering Logic

Notes are displayed only when:
```
Student.Department == Note.Department
AND
Student.Semester == Note.Semester
```

This ensures each student sees only relevant materials for their program.

## 🔄 Like System

- **One-Way Deduplication**: Each student can like a note only once
- **Database Constraint**: Unique(roll_no, note_id) prevents duplicates
- **AJAX Functionality**: Likes are toggled without page reload
- **Like Counter**: Real-time update of like count

## 📥 Download System

- **Secure Download**: Files are downloaded through a verification handler
- **Access Control**: Only students with correct department/semester can download
- **Download Tracking**: Optional logging of downloads in the database
- **File Storage**: Notes stored securely in `/uploads/notes/`

## 🛠️ Customization

### Change Admin Credentials

Edit `database/studyshare.sql`:
```sql
INSERT INTO admin (username, password) VALUES ('newadmin', MD5('newpassword'));
```

### Add New Departments

Edit the select dropdowns in:
- `admin/upload_notes.php`
- `admin/manage_students.php`

### Change Upload Limits

Edit `common/config.php`:
```php
define('MAX_FILE_SIZE', 10 * 1024 * 1024); // Change file size limit
```

### Modify Color Scheme

Edit CSS variables in all CSS files:
```css
:root {
    --primary-color: #6366f1;
    /* Change colors here */
}
```

## 📱 Responsive Breakpoints

- **Desktop**: 1024px and above
- **Tablet**: 768px - 1023px
- **Mobile**: Below 768px

## 🐛 Troubleshooting

### Database Connection Failed
- Verify MySQL is running
- Check credentials in `common/config.php`
- Ensure database name is correct

### File Upload Issues
- Check directory permissions for `uploads/` folder
- Verify file is PDF or DOC format
- Check file size is under 10MB

### Login Issues
- For students: Ensure roll number matches database exactly
- For students: Date of birth must be in YYYY-MM-DD format
- For admin: Default credentials are admin/admin123

### Notes Not Showing
- Verify student's department and semester match note's
- Check notes are uploaded with correct department/semester
- Ensure notes are properly saved in database

## 🚀 Additional Features (Optional)

These features can be easily added:

1. **Search Notes**: Add search functionality on notes.php
2. **Most Liked Notes**: Sorted list by likes
3. **Recent Uploads**: Show newest notes first
4. **Download History**: Track and display user downloads
5. **Email Notifications**: Alert admins on new uploads
6. **Notes Statistics**: Analytics dashboard
7. **Subject Categories**: Organize notes by subjects
8. **User Comments**: Allow students to comment on notes

## 📝 API Endpoints

### Like Toggle
- **URL**: `/user/like.php`
- **Method**: POST
- **Parameters**: `note_id`
- **Response**: JSON with success, liked, and likes count

### Download
- **URL**: `/user/download.php?id={note_id}`
- **Method**: GET
- **Response**: File download

## 📄 License

This project is provided as-is for educational purposes.

## 🤝 Support

For issues or questions, refer to the troubleshooting section or review the code comments for clarification.

---

**Happy Studying with StudyShare! 📚✨**
