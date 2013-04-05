UPDATE user
SET score = :score,
    level = :level
WHERE id = :userID;

INSERT user_action(user_id, score, level, time)
VALUES (:userID, :score, :level, NOW());
    