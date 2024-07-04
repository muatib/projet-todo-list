CREATE TABLE task (
    id_task SERIAL PRIMARY KEY, 
    create_date DATE NOT NULL,
    description VARCHAR(200) NOT NULL,
    is_complete BOOLEAN,         
    display_order SMALLINT       
);

