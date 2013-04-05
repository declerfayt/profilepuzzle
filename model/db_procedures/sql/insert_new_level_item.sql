INSERT INTO level (user_id, level_number, post_id, likes, comments, post_type, time) 
VALUES (:userID, :level, :postID, :likes, :comments, :postType, NOW()); 
