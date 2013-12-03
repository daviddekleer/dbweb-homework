insert into question values(1, "What is the name of the first cheese mentioned
                               in the <i>Cheese Shop</i> sketch of Monty Python?");
insert into question values(2, "What colour is the pigment chlorophyll?");
insert into question values(3, "What type of creature is a gecko?");
insert into question values(4, "Which US president once claimed to have been
                               'misunderestimated'?");
insert into question values(5, "In which country is the Harz mountain range?");

insert into choice values(1, 1, "Gouda", 0);
insert into choice values(1, 2, "Red Leicester", 1); 
insert into choice values(1, 3, "Mozzarella", 0);
insert into choice values(1, 4, "Edam", 0);
insert into choice values(1, 5, "Cheshire", 0);

insert into choice values(2, 1, "Blue", 0);
insert into choice values(2, 2, "Red", 0); 
insert into choice values(2, 3, "Green", 1);
insert into choice values(2, 4, "Purple", 0);
insert into choice values(2, 5, "Orange", 0);

insert into choice values(3, 1, "Bird", 0);
insert into choice values(3, 2, "Fish", 0); 
insert into choice values(3, 3, "Monkey", 0);
insert into choice values(3, 4, "Insect", 0);
insert into choice values(3, 5, "Lizard", 1);

insert into choice values(4, 1, "George W. Bush", 1);
insert into choice values(4, 2, "Jimmy Carter", 0); 
insert into choice values(4, 3, "Barack Obama", 0);
insert into choice values(4, 4, "Gerald Ford", 0);
insert into choice values(4, 5, "Ronald Reagan", 0);

insert into choice values(5, 1, "Austria", 0);
insert into choice values(5, 2, "Switzerland", 0); 
insert into choice values(5, 3, "Spain", 0);
insert into choice values(5, 4, "Germany", 1);
insert into choice values(5, 5, "Belgium", 0);