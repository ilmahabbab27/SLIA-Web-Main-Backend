USE slia_website_db;

-- Insert hero images
INSERT INTO hero_images (image, tag, title, description, link, created_at, updated_at) VALUES
('https://via.placeholder.com/1920x1080?text=Hero+1', 'Featured', 'Sri Lanka Institute of Architects', 'Advancing Architecture and the Built Environment', '/', NOW(), NOW()),
('https://via.placeholder.com/1920x1080?text=Hero+2', 'News & Events', 'Latest Events and Updates', 'Stay connected with our latest news and upcoming events', '/latest-news', NOW(), NOW());

-- Insert events
INSERT INTO events (title, subtitle, description, image, link, category, show_highlight, is_active, sort_order, created_at, updated_at) VALUES
('Annual Conference 2026', 'Architectural Excellence', 'Join us for our annual conference featuring leading architects and industry experts.', 'https://via.placeholder.com/600x400?text=Conference', '/', 'highlight', 1, 1, 1, NOW(), NOW()),
('Design Workshop', 'Professional Development', 'Interactive workshop on sustainable design practices in modern architecture.', 'https://via.placeholder.com/600x400?text=Workshop', '/', 'highlight', 1, 1, 2, NOW(), NOW()),
('Member Networking Event', 'Community Building', 'Connect with fellow architects and expand your professional network.', 'https://via.placeholder.com/600x400?text=Networking', '/', 'program', 0, 1, 3, NOW(), NOW());

-- Insert news items
INSERT INTO news_items (title, subtitle, description, image, is_active, sort_order, created_at, updated_at) VALUES
('New Architecture Standards Released', 'Professional Update', 'SLIA has released updated standards for architectural practice and design excellence.', 'https://via.placeholder.com/600x400?text=News+1', 1, 1, NOW(), NOW()),
('Sustainable Architecture Initiative', 'Green Building', 'Join our initiative to promote sustainable and eco-friendly architectural practices.', 'https://via.placeholder.com/600x400?text=News+2', 1, 2, NOW(), NOW()),
('Student Scholarship Program Opens', 'Education', 'Applications are now open for our prestigious student scholarship program for architecture students.', 'https://via.placeholder.com/600x400?text=News+3', 1, 3, NOW(), NOW());

-- Insert FAQs
INSERT INTO faqs (question, answer, is_active, sort_order, created_at, updated_at) VALUES
('How do I become a registered architect in Sri Lanka?', 'To become a registered architect, you must complete an accredited architecture degree, gain relevant work experience, and pass the professional examination conducted by SLIA.', 1, 1, NOW(), NOW()),
('What are the benefits of SLIA membership?', 'SLIA membership provides professional recognition, access to CPD programs, networking opportunities, and inclusion in the official architects register.', 1, 2, NOW(), NOW()),
('How can I renew my professional license?', 'License renewal is conducted annually. Members must complete required CPD hours and submit renewal fees through our online portal.', 1, 3, NOW(), NOW());
