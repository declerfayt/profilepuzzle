SELECT author_id 
FROM post 
WHERE user_id = :userID 
AND author_id != :userID 
GROUP BY author_id 
ORDER BY RAND() 
LIMIT 1 