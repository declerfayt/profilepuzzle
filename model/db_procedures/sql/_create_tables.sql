CREATE TABLE IF NOT EXISTS user
(
    id   BIGINT UNSIGNED NOT NULL,
    score TINYINT UNSIGNED NOT NULL,
    level TINYINT UNSIGNED NOT NULL,
    language CHAR(2)  NOT NULL, # 2 first letters from 'locale' FB API field
    birthday_date DATE,
    first_name VARCHAR(100),
    last_name VARCHAR(100),
    city VARCHAR(100),
    country VARCHAR(100),
    profile_picture VARCHAR(5000), # pic_square FB API field
    sex BIT, # 1 = male, 0 = female
    
    PRIMARY KEY (id)
)
ENGINE=MyISAM DEFAULT CHARSET=utf8;


##############################################################################


CREATE TABLE IF NOT EXISTS user_action
(
    id   SERIAL,
    user_id  BIGINT UNSIGNED NOT NULL,
    time DATETIME NOT NULL,
    score TINYINT NOT NULL, 
    level TINYINT NOT NULL, 
    
    PRIMARY KEY (id),
    FOREIGN KEY (user_id) REFERENCES user(id)
)
ENGINE=MyISAM;


##############################################################################


CREATE TABLE IF NOT EXISTS post
(
    user_id  BIGINT UNSIGNED NOT NULL,
    author_id BIGINT UNSIGNED NOT NULL,
    post_id BIGINT UNSIGNED NOT NULL,
    post_type VARCHAR(10) NOT NULL,
    
    PRIMARY KEY (user_id, post_id),
    FOREIGN KEY (user_id) REFERENCES user(id)
)
ENGINE=MyISAM;

##############################################################################


CREATE TABLE IF NOT EXISTS level
(
    id SERIAL,
    user_id  BIGINT UNSIGNED NOT NULL,
    level_number TINYINT UNSIGNED NOT NULL,
    post_id BIGINT UNSIGNED NOT NULL,
    post_type VARCHAR(10) NOT NULL,
    likes MEDIUMINT UNSIGNED NOT NULL,
    comments MEDIUMINT UNSIGNED NOT NULL,
    time DATETIME NOT NULL,
    
    PRIMARY KEY (id),
    FOREIGN KEY (user_id) REFERENCES user(id)
)
ENGINE=MyISAM;

##############################################################################


CREATE TABLE IF NOT EXISTS user_taste
(
    id SERIAL,
    user_id  BIGINT UNSIGNED NOT NULL,
    page_id  BIGINT UNSIGNED NOT NULL,
    
    PRIMARY KEY (id),
    FOREIGN KEY (user_id) REFERENCES user(id)
)
ENGINE=MyISAM;