SELECT COUNT(post_id) AS numberOfPosts
FROM post 
WHERE user_id = :userID 
AND author_id = :authorID 
GROUP BY user_id