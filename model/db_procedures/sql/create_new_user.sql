INSERT INTO user (  id, 
                    language, 
                    birthday_date,
                    first_name,
                    last_name,
                    city,
                    country,
                    profile_picture,
                    sex,
                    score,
                    level  )
VALUES (    :id, 
            :language, 
            :birthday_date,
            :first_name,
            :last_name,
            :city,
            :country,
            :profile_picture,
            :sex,
            0,
            1  );


INSERT INTO user_action ( user_id, 
                          score, 
                          level,
                          time)
VALUES ( :id, 
         0,
         1,
         NOW());