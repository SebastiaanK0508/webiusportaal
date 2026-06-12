SELECT 
    table_name AS 'Tabel', 
    column_name AS 'Kolom', 
    data_type AS 'Type', 
    column_type AS 'Detail', 
    is_nullable AS 'Null', 
    column_default AS 'Default'
FROM 
    information_schema.columns 
WHERE 
    table_schema = DATABASE()
ORDER BY 
    table_name, ordinal_position;


+------------------+------------------+-----------+---------------+------+---------------------+
| Tabel            | Kolom            | Type      | Detail        | Null | Default             |
+------------------+------------------+-----------+---------------+------+---------------------+
| app_settings     | id               | char      | char(36)      | NO   | NULL                |
| app_settings     | website_id       | char      | char(36)      | NO   | NULL                |
| app_settings     | setting_key      | varchar   | varchar(100)  | NO   | NULL                |
| app_settings     | setting_value    | text      | text          | YES  | NULL                |
| app_settings     | updated_at       | timestamp | timestamp     | YES  | current_timestamp() |
| categories       | id               | char      | char(36)      | NO   | NULL                |
| categories       | website_id       | char      | char(36)      | NO   | NULL                |
| categories       | name             | varchar   | varchar(255)  | NO   | NULL                |
| categories       | sort_order       | int       | int(11)       | YES  | 0                   |
| contact_messages | id               | char      | char(36)      | NO   | NULL                |
| contact_messages | website_id       | char      | char(36)      | NO   | NULL                |
| contact_messages | name             | varchar   | varchar(255)  | NO   | NULL                |
| contact_messages | email            | varchar   | varchar(255)  | NO   | NULL                |
| contact_messages | phone            | varchar   | varchar(50)   | YES  | NULL                |
| contact_messages | subject          | varchar   | varchar(100)  | YES  | NULL                |
| contact_messages | message          | text      | text          | NO   | NULL                |
| contact_messages | is_read          | tinyint   | tinyint(1)    | YES  | 0                   |
| contact_messages | created_at       | timestamp | timestamp     | YES  | current_timestamp() |
| faqs             | id               | char      | char(36)      | NO   | NULL                |
| faqs             | website_id       | char      | char(36)      | NO   | NULL                |
| faqs             | question         | varchar   | varchar(255)  | NO   | NULL                |
| faqs             | answer           | text      | text          | NO   | NULL                |
| faqs             | created_at       | timestamp | timestamp     | YES  | current_timestamp() |
| footer           | id               | char      | char(36)      | NO   | NULL                |
| footer           | website_id       | char      | char(36)      | NO   | NULL                |
| footer           | sleutel          | varchar   | varchar(50)   | NO   | NULL                |
| footer           | waarde           | text      | text          | YES  | NULL                |
| legals           | id               | char      | char(36)      | NO   | NULL                |
| legals           | website_id       | char      | char(36)      | NO   | NULL                |
| legals           | type             | varchar   | varchar(50)   | NO   | NULL                |
| legals           | title            | varchar   | varchar(255)  | NO   | NULL                |
| legals           | content          | longtext  | longtext      | YES  | NULL                |
| legals           | updated_at       | timestamp | timestamp     | YES  | current_timestamp() |
| portfolio        | id               | char      | char(36)      | NO   | NULL                |
| portfolio        | website_id       | char      | char(36)      | NO   | NULL                |
| portfolio        | title            | varchar   | varchar(255)  | NO   | NULL                |
| portfolio        | description      | text      | text          | YES  | NULL                |
| portfolio        | image_path       | varchar   | varchar(255)  | NO   | NULL                |
| portfolio        | created_at       | timestamp | timestamp     | YES  | current_timestamp() |
| products         | id               | char      | char(36)      | NO   | NULL                |
| products         | website_id       | char      | char(36)      | NO   | NULL                |
| products         | category_id      | char      | char(36)      | YES  | NULL                |
| products         | title            | varchar   | varchar(255)  | NO   | NULL                |
| products         | description      | text      | text          | YES  | NULL                |
| products         | price            | decimal   | decimal(10,2) | NO   | NULL                |
| products         | sort_order       | int       | int(11)       | YES  | 0                   |
| products         | show_on_homepage | tinyint   | tinyint(1)    | YES  | 0                   |
| reviews          | id               | char      | char(36)      | NO   | NULL                |
| reviews          | website_id       | char      | char(36)      | NO   | NULL                |
| reviews          | customer_name    | varchar   | varchar(255)  | NO   | NULL                |
| reviews          | review_text      | text      | text          | NO   | NULL                |
| reviews          | stars            | int       | int(11)       | YES  | 5                   |
| reviews          | created_at       | timestamp | timestamp     | YES  | current_timestamp() |
| services         | id               | char      | char(36)      | NO   | NULL                |
| services         | website_id       | char      | char(36)      | NO   | NULL                |
| services         | title            | varchar   | varchar(255)  | NO   | NULL                |
| services         | description      | text      | text          | NO   | NULL                |
| services         | price            | decimal   | decimal(10,2) | NO   | NULL                |
| services         | created_at       | timestamp | timestamp     | YES  | current_timestamp() |
| site_content     | id               | char      | char(36)      | NO   | NULL                |
| site_content     | website_id       | char      | char(36)      | NO   | NULL                |
| site_content     | page             | varchar   | varchar(50)   | NO   | NULL                |
| site_content     | section_key      | varchar   | varchar(50)   | NO   | NULL                |
| site_content     | content_text     | text      | text          | NO   | NULL                |
| site_content     | is_visible       | tinyint   | tinyint(1)    | YES  | 1                   |
| site_content     | updated_at       | timestamp | timestamp     | YES  | current_timestamp() |
| users            | id               | char      | char(36)      | NO   | NULL                |
| users            | website_id       | char      | char(36)      | YES  | NULL                |
| users            | username         | varchar   | varchar(50)   | NO   | NULL                |
| users            | email            | varchar   | varchar(255)  | YES  | NULL                |
| users            | password_hash    | varchar   | varchar(255)  | NO   | NULL                |
| users            | reset_token      | varchar   | varchar(64)   | YES  | NULL                |
| users            | reset_expires    | datetime  | datetime      | YES  | NULL                |
| users            | last_login       | timestamp | timestamp     | YES  | NULL                |
| users            | created_at       | timestamp | timestamp     | YES  | current_timestamp() |
| usps             | id               | char      | char(36)      | NO   | NULL                |
| usps             | website_id       | char      | char(36)      | NO   | NULL                |
| usps             | icon             | varchar   | varchar(50)   | NO   | NULL                |
| usps             | title            | varchar   | varchar(255)  | NO   | NULL                |
| usps             | description      | text      | text          | NO   | NULL                |
| usps             | created_at       | timestamp | timestamp     | YES  | current_timestamp() |
| websites         | id               | char      | char(36)      | NO   | NULL                |
| websites         | domain_name      | varchar   | varchar(255)  | NO   | NULL                |
| websites         | company_name     | varchar   | varchar(255)  | NO   | NULL                |
| websites         | is_active        | tinyint   | tinyint(1)    | YES  | 1                   |
| websites         | created_at       | timestamp | timestamp     | YES  | current_timestamp() |
+------------------+------------------+-----------+---------------+------+---------------------+