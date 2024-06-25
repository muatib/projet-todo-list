CREATE TABLE task(
   Id_task COUNTER,
   create_date DATE NOT NULL,
   description VARCHAR(200) NOT NULL,
   check_complete LOGICAL,
   display_order BYTE,
   PRIMARY KEY(Id_task)
);
