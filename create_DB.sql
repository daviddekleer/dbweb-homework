create table question(q_number int,
                      q_text varchar(200),
                      primary key(q_number));

create table choice(q_number int,
                    c_number tinyint, -- only 5 alternatives for a question
                    c_text varchar(100),
                    correct tinyint, -- made this tinyint instead of bit, because bit doesn't display correctly
                    primary key(q_number, c_number),
                    foreign key(q_number) references question(q_number));

create table user(name varchar(20),
                  password varchar(255), -- store a hash!
                  primary key(name));

create table history(u_name varchar(20),
                     time_taken int,
                     score int,
                     time_started int,
                     primary key(u_name, time_taken, score, time_started),
                     foreign key(u_name) references user(name));
                     
                                               