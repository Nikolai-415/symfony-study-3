
ALTER TABLE vacancies ALTER COLUMN id RESTART SET START 10;

INSERT INTO vacancies(parent, id, name) VALUES (0, 1,   'Категория 1');
INSERT INTO vacancies(parent, id, name) VALUES (1, 2,       'Категория 1.1');
INSERT INTO vacancies(parent, id, name) VALUES (1, 3,       'Категория 1.2');
INSERT INTO vacancies(parent, name)     VALUES (3,              'Вакансия 1.2.1');
INSERT INTO vacancies(parent, name)     VALUES (3,              'Вакансия 1.2.2');
INSERT INTO vacancies(parent, name)     VALUES (1,          'Вакансия 1.1');
INSERT INTO vacancies(parent, name)     VALUES (1,          'Вакансия 1.2');
INSERT INTO vacancies(parent, id, name) VALUES (0, 4,   'Категория 2');
INSERT INTO vacancies(parent, name)     VALUES (4,          'Вакансия 2.1');
INSERT INTO vacancies(parent, name)     VALUES (4,          'Вакансия 2.2');
INSERT INTO vacancies(parent, id, name) VALUES (0, 5,   'Категория 3');
INSERT INTO vacancies(parent, id, name) VALUES (5, 6,       'Категория 3.1');
INSERT INTO vacancies(parent, name)     VALUES (6,              'Вакансия 3.1.1');
INSERT INTO vacancies(parent, id, name) VALUES (5, 7,       'Категория 3.2');
INSERT INTO vacancies(parent, id, name) VALUES (7, 8,           'Категория 3.2.1');
INSERT INTO vacancies(parent, name)     VALUES (8,                  'Вакансия 3.2.1.1');
INSERT INTO vacancies(parent, name)     VALUES (8,                  'Вакансия 3.2.1.2');
INSERT INTO vacancies(parent, id, name) VALUES (7, 9,           'Категория 3.2.2');
INSERT INTO vacancies(parent, name)     VALUES (9,                  'Вакансия 3.2.2.1');
INSERT INTO vacancies(parent, name)     VALUES (9,                  'Вакансия 3.2.2.2');
INSERT INTO vacancies(parent, name)     VALUES (9,                  'Вакансия 3.2.2.3');
INSERT INTO vacancies(parent, name)     VALUES (5,          'Вакансия 3.1');