-- Seed Dummy Data for Course Review Hub
-- Run this SQL script in MySQL to populate tables with sample data
-- Make sure you've already run schema.sql first!

USE course_review_hub;

-- Step 1: Insert courses (using INSERT IGNORE to avoid duplicates)
INSERT IGNORE INTO courses (code, title, description) VALUES
('EECS 183', 'Elementary Programming Concepts', 'Introduction to computer programming. Covers basic programming concepts including variables, functions, control structures, and simple data structures. Perfect for beginners with no prior programming experience.'),
('EECS 280', 'Programming and Introductory Data Structures', 'Intermediate programming concepts with focus on C++. Covers pointers, memory management, data structures (arrays, linked lists, trees), and algorithms. Essential course for all CS majors.'),
('EECS 281', 'Data Structures and Algorithms', 'Advanced data structures and algorithm analysis. Topics include hash tables, graphs, sorting algorithms, dynamic programming, and algorithmic complexity. Challenging but rewarding course.'),
('EECS 370', 'Introduction to Computer Organization', 'Computer architecture and organization. Covers assembly language, processor design, memory systems, and computer organization. Requires EECS 280.'),
('EECS 445', 'Machine Learning', 'Introduction to machine learning algorithms and applications. Covers supervised learning, unsupervised learning, neural networks, and deep learning fundamentals.'),
('EECS 484', 'Database Management Systems', 'Principles of database design and management. Covers SQL, database design, transactions, indexing, and query optimization. Great practical skills for industry.'),
('EECS 485', 'Web Systems', 'Full-stack web development. Covers frontend (HTML, CSS, JavaScript), backend (server-side programming), databases, and web application architecture.');

-- Step 2: Create a test user for reviews (if doesn't exist)
-- Note: You can change this email/password or use an existing user ID
INSERT IGNORE INTO users (name, email, password_hash) VALUES
('Test User', 'test@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');
-- Password for test user: "password"

-- Step 3: Get the test user ID (you'll need this for reviews)
-- Run this query first to get the user ID:
-- SELECT id FROM users WHERE email = 'test@example.com';

-- Alternative: If you want to use your own user account, replace @userId with your actual user ID below

-- For now, assuming test user has ID = 1 (adjust if needed)
SET @userId = (SELECT id FROM users WHERE email = 'test@example.com' LIMIT 1);

-- If test user doesn't exist, create it and get ID
INSERT IGNORE INTO users (name, email, password_hash) VALUES
('Test User', 'test@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

SET @userId = (SELECT id FROM users WHERE email = 'test@example.com' LIMIT 1);

-- Step 4: Insert reviews for each course
-- Get course IDs first
SET @eecs183 = (SELECT id FROM courses WHERE code = 'EECS 183' LIMIT 1);
SET @eecs280 = (SELECT id FROM courses WHERE code = 'EECS 280' LIMIT 1);
SET @eecs281 = (SELECT id FROM courses WHERE code = 'EECS 281' LIMIT 1);
SET @eecs370 = (SELECT id FROM courses WHERE code = 'EECS 370' LIMIT 1);
SET @eecs445 = (SELECT id FROM courses WHERE code = 'EECS 445' LIMIT 1);
SET @eecs484 = (SELECT id FROM courses WHERE code = 'EECS 484' LIMIT 1);
SET @eecs485 = (SELECT id FROM courses WHERE code = 'EECS 485' LIMIT 1);

-- Insert reviews with semester_taken
INSERT INTO reviews (course_id, user_id, rating, comment, semester_taken, created_at) VALUES
-- EECS 183 reviews
(@eecs183, @userId, 5, 'Great introduction to programming! The professors are very helpful and the material is well-structured.', 'Fall 2024', NOW()),
(@eecs183, @userId, 4, 'Solid foundation course. Some assignments can be challenging but very manageable.', 'Spring 2024', NOW()),

-- EECS 280 reviews
(@eecs280, @userId, 5, 'Excellent course! Really solidifies your programming skills. Projects are challenging but fair.', 'Fall 2024', NOW()),
(@eecs280, @userId, 4, 'Good course but workload is heavy. Make sure to start projects early!', 'Winter 2024', NOW()),
(@eecs280, @userId, 5, 'Loved this course! The staff is amazing and the projects are well-designed.', 'Spring 2024', NOW()),

-- EECS 281 reviews
(@eecs281, @userId, 5, 'Tough but rewarding! You learn a lot about algorithms and data structures.', 'Fall 2024', NOW()),
(@eecs281, @userId, 4, 'Challenging course but essential for interviews. Exams are tough but fair.', 'Winter 2024', NOW()),

-- EECS 370 reviews
(@eecs370, @userId, 4, 'Interesting material on computer architecture. Assembly language can be tricky.', 'Fall 2024', NOW()),
(@eecs370, @userId, 3, 'Good content but very theoretical. Lots of reading required.', 'Spring 2024', NOW()),

-- EECS 445 reviews
(@eecs445, @userId, 5, 'Amazing introduction to ML! Projects are engaging and you learn a lot.', 'Fall 2024', NOW()),
(@eecs445, @userId, 4, 'Great course but requires strong math background. Group projects are fun!', 'Winter 2024', NOW()),

-- EECS 484 reviews
(@eecs484, @userId, 5, 'Very practical course! SQL skills are directly applicable in industry.', 'Fall 2024', NOW()),
(@eecs484, @userId, 4, 'Good balance of theory and practice. Projects help you understand databases well.', 'Spring 2024', NOW()),

-- EECS 485 reviews
(@eecs485, @userId, 5, 'Best course I\'ve taken! You build a real web application and learn modern frameworks.', 'Fall 2024', NOW()),
(@eecs485, @userId, 5, 'Fantastic full-stack course. Projects are challenging but you learn so much!', 'Winter 2024', NOW()),
(@eecs485, @userId, 4, 'Great course! Workload is heavy but very rewarding. Group work is well-organized.', 'Spring 2024', NOW());

-- Step 5: Get tag IDs (tags should already exist from schema.sql)
SET @tagHeavyReading = (SELECT id FROM tags WHERE name = 'Heavy Reading' LIMIT 1);
SET @tagPopQuizzes = (SELECT id FROM tags WHERE name = 'Pop Quizzes' LIMIT 1);
SET @tagGroupProjects = (SELECT id FROM tags WHERE name = 'Group Projects' LIMIT 1);
SET @tagAttendance = (SELECT id FROM tags WHERE name = 'Attendance Matters' LIMIT 1);
SET @tagLightWorkload = (SELECT id FROM tags WHERE name = 'Light Workload' LIMIT 1);
SET @tagProjectBased = (SELECT id FROM tags WHERE name = 'Project Based' LIMIT 1);
SET @tagExamHeavy = (SELECT id FROM tags WHERE name = 'Exam Heavy' LIMIT 1);

-- Step 6: Link reviews to tags
-- Get review IDs (we'll link based on comment text and course)
-- EECS 183 - Review 1: "Great introduction..."
INSERT INTO review_tags (review_id, tag_id)
SELECT r.id, @tagLightWorkload FROM reviews r WHERE r.comment LIKE 'Great introduction%' AND r.course_id = @eecs183 LIMIT 1;

INSERT INTO review_tags (review_id, tag_id)
SELECT r.id, @tagProjectBased FROM reviews r WHERE r.comment LIKE 'Great introduction%' AND r.course_id = @eecs183 LIMIT 1;

-- EECS 183 - Review 2: "Solid foundation..."
INSERT INTO review_tags (review_id, tag_id)
SELECT r.id, @tagProjectBased FROM reviews r WHERE r.comment LIKE 'Solid foundation%' AND r.course_id = @eecs183 LIMIT 1;

-- EECS 280 - Review 1: "Excellent course!"
INSERT INTO review_tags (review_id, tag_id)
SELECT r.id, @tagProjectBased FROM reviews r WHERE r.comment LIKE 'Excellent course!%' AND r.course_id = @eecs280 LIMIT 1;

INSERT INTO review_tags (review_id, tag_id)
SELECT r.id, @tagHeavyReading FROM reviews r WHERE r.comment LIKE 'Excellent course!%' AND r.course_id = @eecs280 LIMIT 1;

-- EECS 280 - Review 2: "Good course but workload..."
INSERT INTO review_tags (review_id, tag_id)
SELECT r.id, @tagProjectBased FROM reviews r WHERE r.comment LIKE 'Good course but workload%' AND r.course_id = @eecs280 LIMIT 1;

INSERT INTO review_tags (review_id, tag_id)
SELECT r.id, @tagHeavyReading FROM reviews r WHERE r.comment LIKE 'Good course but workload%' AND r.course_id = @eecs280 LIMIT 1;

-- EECS 280 - Review 3: "Loved this course!"
INSERT INTO review_tags (review_id, tag_id)
SELECT r.id, @tagProjectBased FROM reviews r WHERE r.comment LIKE 'Loved this course!%' AND r.course_id = @eecs280 LIMIT 1;

-- EECS 281 - Review 1: "Tough but rewarding!"
INSERT INTO review_tags (review_id, tag_id)
SELECT r.id, @tagExamHeavy FROM reviews r WHERE r.comment LIKE 'Tough but rewarding!%' AND r.course_id = @eecs281 LIMIT 1;

INSERT INTO review_tags (review_id, tag_id)
SELECT r.id, @tagHeavyReading FROM reviews r WHERE r.comment LIKE 'Tough but rewarding!%' AND r.course_id = @eecs281 LIMIT 1;

-- EECS 281 - Review 2: "Challenging course but essential..."
INSERT INTO review_tags (review_id, tag_id)
SELECT r.id, @tagExamHeavy FROM reviews r WHERE r.comment LIKE 'Challenging course but essential%' AND r.course_id = @eecs281 LIMIT 1;

INSERT INTO review_tags (review_id, tag_id)
SELECT r.id, @tagProjectBased FROM reviews r WHERE r.comment LIKE 'Challenging course but essential%' AND r.course_id = @eecs281 LIMIT 1;

-- EECS 370 - Review 1: "Interesting material..."
INSERT INTO review_tags (review_id, tag_id)
SELECT r.id, @tagHeavyReading FROM reviews r WHERE r.comment LIKE 'Interesting material%' AND r.course_id = @eecs370 LIMIT 1;

INSERT INTO review_tags (review_id, tag_id)
SELECT r.id, @tagProjectBased FROM reviews r WHERE r.comment LIKE 'Interesting material%' AND r.course_id = @eecs370 LIMIT 1;

-- EECS 370 - Review 2: "Good content but very theoretical..."
INSERT INTO review_tags (review_id, tag_id)
SELECT r.id, @tagHeavyReading FROM reviews r WHERE r.comment LIKE 'Good content but very theoretical%' AND r.course_id = @eecs370 LIMIT 1;

-- EECS 445 - Review 1: "Amazing introduction to ML!"
INSERT INTO review_tags (review_id, tag_id)
SELECT r.id, @tagProjectBased FROM reviews r WHERE r.comment LIKE 'Amazing introduction to ML!%' AND r.course_id = @eecs445 LIMIT 1;

INSERT INTO review_tags (review_id, tag_id)
SELECT r.id, @tagGroupProjects FROM reviews r WHERE r.comment LIKE 'Amazing introduction to ML!%' AND r.course_id = @eecs445 LIMIT 1;

-- EECS 445 - Review 2: "Great course but requires strong math..."
INSERT INTO review_tags (review_id, tag_id)
SELECT r.id, @tagGroupProjects FROM reviews r WHERE r.comment LIKE 'Great course but requires strong math%' AND r.course_id = @eecs445 LIMIT 1;

INSERT INTO review_tags (review_id, tag_id)
SELECT r.id, @tagHeavyReading FROM reviews r WHERE r.comment LIKE 'Great course but requires strong math%' AND r.course_id = @eecs445 LIMIT 1;

-- EECS 484 - Review 1: "Very practical course!"
INSERT INTO review_tags (review_id, tag_id)
SELECT r.id, @tagProjectBased FROM reviews r WHERE r.comment LIKE 'Very practical course!%' AND r.course_id = @eecs484 LIMIT 1;

INSERT INTO review_tags (review_id, tag_id)
SELECT r.id, @tagLightWorkload FROM reviews r WHERE r.comment LIKE 'Very practical course!%' AND r.course_id = @eecs484 LIMIT 1;

-- EECS 484 - Review 2: "Good balance of theory..."
INSERT INTO review_tags (review_id, tag_id)
SELECT r.id, @tagProjectBased FROM reviews r WHERE r.comment LIKE 'Good balance of theory%' AND r.course_id = @eecs484 LIMIT 1;

-- EECS 485 - Review 1: "Best course I've taken!"
INSERT INTO review_tags (review_id, tag_id)
SELECT r.id, @tagProjectBased FROM reviews r WHERE r.comment LIKE 'Best course I\'ve taken!%' AND r.course_id = @eecs485 LIMIT 1;

INSERT INTO review_tags (review_id, tag_id)
SELECT r.id, @tagGroupProjects FROM reviews r WHERE r.comment LIKE 'Best course I\'ve taken!%' AND r.course_id = @eecs485 LIMIT 1;

-- EECS 485 - Review 2: "Fantastic full-stack course..."
INSERT INTO review_tags (review_id, tag_id)
SELECT r.id, @tagProjectBased FROM reviews r WHERE r.comment LIKE 'Fantastic full-stack course%' AND r.course_id = @eecs485 LIMIT 1;

INSERT INTO review_tags (review_id, tag_id)
SELECT r.id, @tagGroupProjects FROM reviews r WHERE r.comment LIKE 'Fantastic full-stack course%' AND r.course_id = @eecs485 LIMIT 1;

-- EECS 485 - Review 3: "Great course! Workload..."
INSERT INTO review_tags (review_id, tag_id)
SELECT r.id, @tagGroupProjects FROM reviews r WHERE r.comment LIKE 'Great course! Workload%' AND r.course_id = @eecs485 LIMIT 1;

INSERT INTO review_tags (review_id, tag_id)
SELECT r.id, @tagHeavyReading FROM reviews r WHERE r.comment LIKE 'Great course! Workload%' AND r.course_id = @eecs485 LIMIT 1;

-- Verification queries (optional - run these to check if data was inserted)
-- SELECT COUNT(*) as course_count FROM courses;
-- SELECT COUNT(*) as review_count FROM reviews;
-- SELECT COUNT(*) as tag_count FROM review_tags;
-- SELECT * FROM courses;
-- SELECT r.*, c.code as course_code FROM reviews r JOIN courses c ON r.course_id = c.id;

