-- SIMPLE VERSION: Seed Dummy Data for Course Review Hub
-- Copy and paste this entire file into MySQL (phpMyAdmin or MySQL Workbench)
-- Make sure you've run schema.sql first!

USE course_review_hub;

-- 1. Insert Courses (7 courses)
INSERT IGNORE INTO courses (code, title, description) VALUES
('EECS 183', 'Elementary Programming Concepts', 'Introduction to computer programming. Covers basic programming concepts including variables, functions, control structures, and simple data structures. Perfect for beginners with no prior programming experience.'),
('EECS 280', 'Programming and Introductory Data Structures', 'Intermediate programming concepts with focus on C++. Covers pointers, memory management, data structures (arrays, linked lists, trees), and algorithms. Essential course for all CS majors.'),
('EECS 281', 'Data Structures and Algorithms', 'Advanced data structures and algorithm analysis. Topics include hash tables, graphs, sorting algorithms, dynamic programming, and algorithmic complexity. Challenging but rewarding course.'),
('EECS 370', 'Introduction to Computer Organization', 'Computer architecture and organization. Covers assembly language, processor design, memory systems, and computer organization. Requires EECS 280.'),
('EECS 445', 'Machine Learning', 'Introduction to machine learning algorithms and applications. Covers supervised learning, unsupervised learning, neural networks, and deep learning fundamentals.'),
('EECS 484', 'Database Management Systems', 'Principles of database design and management. Covers SQL, database design, transactions, indexing, and query optimization. Great practical skills for industry.'),
('EECS 485', 'Web Systems', 'Full-stack web development. Covers frontend (HTML, CSS, JavaScript), backend (server-side programming), databases, and web application architecture.');

-- 2. Create Test User (if doesn't exist)
-- Password is "password" (hashed with bcrypt)
INSERT IGNORE INTO users (id, name, email, password_hash) VALUES
(1, 'Test User', 'test@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- 3. Insert Reviews
-- First, get course IDs by looking them up, then insert reviews
-- Using course codes to find IDs dynamically

INSERT INTO reviews (course_id, user_id, rating, comment, semester_taken, created_at)
SELECT id, 1, 5, 'Great introduction to programming! The professors are very helpful and the material is well-structured.', 'Fall 2024', NOW()
FROM courses WHERE code = 'EECS 183';

INSERT INTO reviews (course_id, user_id, rating, comment, semester_taken, created_at)
SELECT id, 1, 4, 'Solid foundation course. Some assignments can be challenging but very manageable.', 'Spring 2024', NOW()
FROM courses WHERE code = 'EECS 183';

INSERT INTO reviews (course_id, user_id, rating, comment, semester_taken, created_at)
SELECT id, 1, 5, 'Excellent course! Really solidifies your programming skills. Projects are challenging but fair.', 'Fall 2024', NOW()
FROM courses WHERE code = 'EECS 280';

INSERT INTO reviews (course_id, user_id, rating, comment, semester_taken, created_at)
SELECT id, 1, 4, 'Good course but workload is heavy. Make sure to start projects early!', 'Winter 2024', NOW()
FROM courses WHERE code = 'EECS 280';

INSERT INTO reviews (course_id, user_id, rating, comment, semester_taken, created_at)
SELECT id, 1, 5, 'Loved this course! The staff is amazing and the projects are well-designed.', 'Spring 2024', NOW()
FROM courses WHERE code = 'EECS 280';

INSERT INTO reviews (course_id, user_id, rating, comment, semester_taken, created_at)
SELECT id, 1, 5, 'Tough but rewarding! You learn a lot about algorithms and data structures.', 'Fall 2024', NOW()
FROM courses WHERE code = 'EECS 281';

INSERT INTO reviews (course_id, user_id, rating, comment, semester_taken, created_at)
SELECT id, 1, 4, 'Challenging course but essential for interviews. Exams are tough but fair.', 'Winter 2024', NOW()
FROM courses WHERE code = 'EECS 281';

INSERT INTO reviews (course_id, user_id, rating, comment, semester_taken, created_at)
SELECT id, 1, 4, 'Interesting material on computer architecture. Assembly language can be tricky.', 'Fall 2024', NOW()
FROM courses WHERE code = 'EECS 370';

INSERT INTO reviews (course_id, user_id, rating, comment, semester_taken, created_at)
SELECT id, 1, 3, 'Good content but very theoretical. Lots of reading required.', 'Spring 2024', NOW()
FROM courses WHERE code = 'EECS 370';

INSERT INTO reviews (course_id, user_id, rating, comment, semester_taken, created_at)
SELECT id, 1, 5, 'Amazing introduction to ML! Projects are engaging and you learn a lot.', 'Fall 2024', NOW()
FROM courses WHERE code = 'EECS 445';

INSERT INTO reviews (course_id, user_id, rating, comment, semester_taken, created_at)
SELECT id, 1, 4, 'Great course but requires strong math background. Group projects are fun!', 'Winter 2024', NOW()
FROM courses WHERE code = 'EECS 445';

INSERT INTO reviews (course_id, user_id, rating, comment, semester_taken, created_at)
SELECT id, 1, 5, 'Very practical course! SQL skills are directly applicable in industry.', 'Fall 2024', NOW()
FROM courses WHERE code = 'EECS 484';

INSERT INTO reviews (course_id, user_id, rating, comment, semester_taken, created_at)
SELECT id, 1, 4, 'Good balance of theory and practice. Projects help you understand databases well.', 'Spring 2024', NOW()
FROM courses WHERE code = 'EECS 484';

INSERT INTO reviews (course_id, user_id, rating, comment, semester_taken, created_at)
SELECT id, 1, 5, 'Best course I''ve taken! You build a real web application and learn modern frameworks.', 'Fall 2024', NOW()
FROM courses WHERE code = 'EECS 485';

INSERT INTO reviews (course_id, user_id, rating, comment, semester_taken, created_at)
SELECT id, 1, 5, 'Fantastic full-stack course. Projects are challenging but you learn so much!', 'Winter 2024', NOW()
FROM courses WHERE code = 'EECS 485';

INSERT INTO reviews (course_id, user_id, rating, comment, semester_taken, created_at)
SELECT id, 1, 4, 'Great course! Workload is heavy but very rewarding. Group work is well-organized.', 'Spring 2024', NOW()
FROM courses WHERE code = 'EECS 485';

-- 4. Link Reviews to Tags
-- Tags should already exist from schema.sql
-- We'll link reviews based on course and comment text

INSERT INTO review_tags (review_id, tag_id)
SELECT r.id, t.id FROM reviews r, courses c, tags t
WHERE r.course_id = c.id AND c.code = 'EECS 183' 
AND r.comment LIKE 'Great introduction%'
AND t.name IN ('Light Workload', 'Project Based');

INSERT INTO review_tags (review_id, tag_id)
SELECT r.id, t.id FROM reviews r, courses c, tags t
WHERE r.course_id = c.id AND c.code = 'EECS 183' 
AND r.comment LIKE 'Solid foundation%'
AND t.name = 'Project Based';

INSERT INTO review_tags (review_id, tag_id)
SELECT r.id, t.id FROM reviews r, courses c, tags t
WHERE r.course_id = c.id AND c.code = 'EECS 280' 
AND r.comment LIKE 'Excellent course!%'
AND t.name IN ('Project Based', 'Heavy Reading');

INSERT INTO review_tags (review_id, tag_id)
SELECT r.id, t.id FROM reviews r, courses c, tags t
WHERE r.course_id = c.id AND c.code = 'EECS 280' 
AND r.comment LIKE 'Good course but workload%'
AND t.name IN ('Project Based', 'Heavy Reading');

INSERT INTO review_tags (review_id, tag_id)
SELECT r.id, t.id FROM reviews r, courses c, tags t
WHERE r.course_id = c.id AND c.code = 'EECS 280' 
AND r.comment LIKE 'Loved this course!%'
AND t.name = 'Project Based';

INSERT INTO review_tags (review_id, tag_id)
SELECT r.id, t.id FROM reviews r, courses c, tags t
WHERE r.course_id = c.id AND c.code = 'EECS 281' 
AND r.comment LIKE 'Tough but rewarding!%'
AND t.name IN ('Exam Heavy', 'Heavy Reading');

INSERT INTO review_tags (review_id, tag_id)
SELECT r.id, t.id FROM reviews r, courses c, tags t
WHERE r.course_id = c.id AND c.code = 'EECS 281' 
AND r.comment LIKE 'Challenging course but essential%'
AND t.name IN ('Exam Heavy', 'Project Based');

INSERT INTO review_tags (review_id, tag_id)
SELECT r.id, t.id FROM reviews r, courses c, tags t
WHERE r.course_id = c.id AND c.code = 'EECS 370' 
AND r.comment LIKE 'Interesting material%'
AND t.name IN ('Heavy Reading', 'Project Based');

INSERT INTO review_tags (review_id, tag_id)
SELECT r.id, t.id FROM reviews r, courses c, tags t
WHERE r.course_id = c.id AND c.code = 'EECS 370' 
AND r.comment LIKE 'Good content but very theoretical%'
AND t.name = 'Heavy Reading';

INSERT INTO review_tags (review_id, tag_id)
SELECT r.id, t.id FROM reviews r, courses c, tags t
WHERE r.course_id = c.id AND c.code = 'EECS 445' 
AND r.comment LIKE 'Amazing introduction to ML!%'
AND t.name IN ('Project Based', 'Group Projects');

INSERT INTO review_tags (review_id, tag_id)
SELECT r.id, t.id FROM reviews r, courses c, tags t
WHERE r.course_id = c.id AND c.code = 'EECS 445' 
AND r.comment LIKE 'Great course but requires strong math%'
AND t.name IN ('Group Projects', 'Heavy Reading');

INSERT INTO review_tags (review_id, tag_id)
SELECT r.id, t.id FROM reviews r, courses c, tags t
WHERE r.course_id = c.id AND c.code = 'EECS 484' 
AND r.comment LIKE 'Very practical course!%'
AND t.name IN ('Project Based', 'Light Workload');

INSERT INTO review_tags (review_id, tag_id)
SELECT r.id, t.id FROM reviews r, courses c, tags t
WHERE r.course_id = c.id AND c.code = 'EECS 484' 
AND r.comment LIKE 'Good balance of theory%'
AND t.name = 'Project Based';

INSERT INTO review_tags (review_id, tag_id)
SELECT r.id, t.id FROM reviews r, courses c, tags t
WHERE r.course_id = c.id AND c.code = 'EECS 485' 
AND r.comment LIKE 'Best course I''ve taken!%'
AND t.name IN ('Project Based', 'Group Projects');

INSERT INTO review_tags (review_id, tag_id)
SELECT r.id, t.id FROM reviews r, courses c, tags t
WHERE r.course_id = c.id AND c.code = 'EECS 485' 
AND r.comment LIKE 'Fantastic full-stack course%'
AND t.name IN ('Project Based', 'Group Projects');

INSERT INTO review_tags (review_id, tag_id)
SELECT r.id, t.id FROM reviews r, courses c, tags t
WHERE r.course_id = c.id AND c.code = 'EECS 485' 
AND r.comment LIKE 'Great course! Workload%'
AND t.name IN ('Group Projects', 'Heavy Reading');

-- Verification (run these to check data):
-- SELECT COUNT(*) as course_count FROM courses;
-- SELECT COUNT(*) as review_count FROM reviews;
-- SELECT COUNT(*) as tag_count FROM review_tags;
-- SELECT * FROM courses;
-- SELECT r.*, c.code FROM reviews r JOIN courses c ON r.course_id = c.id;

