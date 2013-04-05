#   SELECT RANDOM POST PROCEDURE
#
#   1 chance to pick a non-photo post from current user's friend
#   2 times more chance to pick a photo post from current user's friend
#   4 times more chance to pick a non-photo current user own post
#   8 times more chance to pick a photo current user own post
#
#
##########################################


DROP PROCEDURE IF EXISTS select_random_post;

DELIMITER |
CREATE PROCEDURE select_random_post(IN userID BIGINT UNSIGNED)
BEGIN


##########################################


DECLARE i INT DEFAULT 0;


##########################################
 

DROP TABLE IF EXISTS temp_result;

CREATE TEMPORARY TABLE temp_result
(
    user_id  BIGINT UNSIGNED NOT NULL,
    author_id BIGINT UNSIGNED NOT NULL,
    post_id BIGINT UNSIGNED NOT NULL,
    post_type VARCHAR(10) NOT NULL
)
ENGINE=MEMORY;


##########################################


INSERT INTO temp_result (user_id, author_id, post_id, post_type)       
SELECT user_id, author_id, post_id, post_type 
FROM post 
WHERE user_id = userID;


########


INSERT INTO temp_result (user_id, author_id, post_id, post_type)       
SELECT user_id, author_id, post_id, post_type 
FROM post 
WHERE user_id = userID
AND post_type LIKE 'photo%';


##########################################

	
# WHILE i < 2 DO

	INSERT INTO temp_result (user_id, author_id, post_id, post_type)      
	SELECT user_id, author_id, post_id, post_type 
	FROM post 
	WHERE user_id = userID
        AND author_id = userID;

	INSERT INTO temp_result (user_id, author_id, post_id, post_type)      
	SELECT user_id, author_id, post_id, post_type 
	FROM post 
	WHERE user_id = userID
        AND author_id = userID
	AND post_type LIKE 'photo%';  
 
        SET i = i + 1;

# END WHILE;


##########################################


SELECT  user_id, author_id, post_id, post_type
FROM temp_result ORDER BY RAND() LIMIT 1;


DROP TABLE temp_result;

##########################################


END |
DELIMITER ; 