SELECT likes + (2 * comments) AS score, 
       post_id, 
       post_type,  
       likes, 
       comments 
FROM level 
WHERE user_id = :userID 
AND level_number = :level 
ORDER BY score DESC 
LIMIT 20 