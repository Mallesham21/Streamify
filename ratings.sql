CREATE TABLE ratings (
    rating_id INT PRIMARY KEY AUTO_INCREMENT,
    content_id INT NOT NULL,
    user_id INT NOT NULL,
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (content_id) REFERENCES content(content_id),
    FOREIGN KEY (user_id) REFERENCES users(user_id),
    UNIQUE KEY unique_rating (content_id, user_id)
);

-- Add view for rating statistics
CREATE VIEW rating_stats AS
SELECT 
    content_id,
    COUNT(*) as total_ratings,
    ROUND(AVG(rating), 1) as average_rating,
    SUM(CASE WHEN rating = 5 THEN 1 ELSE 0 END) as five_star_count,
    SUM(CASE WHEN rating = 4 THEN 1 ELSE 0 END) as four_star_count,
    SUM(CASE WHEN rating = 3 THEN 1 ELSE 0 END) as three_star_count,
    SUM(CASE WHEN rating = 2 THEN 1 ELSE 0 END) as two_star_count,
    SUM(CASE WHEN rating = 1 THEN 1 ELSE 0 END) as one_star_count
FROM ratings
GROUP BY content_id;