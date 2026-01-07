
create database maisonTravaux;
use maisonTravaux;

create table type_maison (
    id int primary key auto_increment,
    type_maison VARCHAR(10),
    description TEXT,
    surface DECIMAL(10,2)
);

create table code_travaux (
    id int PRIMARY key auto_increment,
    code_travaux INT,
    type_travaux VARCHAR(50),
    prix_unitaire DECIMAL(10,2)   
);

create table unite (
    id int PRIMARY key auto_increment,
    unite VARCHAR(5)
);

create table travaux (
    id int PRIMARY key auto_increment,
    id_maison int,
    id_travaux int,
    id_unite int,
    quantite int,
    duree int,
    FOREIGN KEY (id_maison) REFERENCES type_maison (id),
    FOREIGN KEY (id_travaux) REFERENCES code_travaux (id),
    FOREIGN key (id_unite) REFERENCES Unite (id)
);

insert into type_maison (type_maison,description,surface) values ('TOKYO','2 chambres, 1 living, 1 salle de bain',128);
insert into type_maison (type_maison,description,surface) values ('KINSHASA','4 chambres, 1 living, 2 salles de bain, 1 garage',150);
insert into type_maison (type_maison,description,surface) values ('LONDRES','3 chambres, 1 terrasse, 1 salle de bain',101);

insert into code_travaux (code_travaux,type_travaux,prix_unitaire) values 
(101,'Travaux d''implantation',152656),
(102,'beton armée dosée à 350kg/m3',573215.8),
(103,'Armature pour Béton',8500),
(105,'Mur 22cm',45000),
(201,'maçonnerie de moellons',172114.4),
(203,'chape de 2cm',33566.54),
(302,'Peinture intérieure',10060.51),
(303,'Peinture extérieure',10060.51),
(401,'chassis vitrées',602000);

insert into unite (unite)  values ('m3'),('m2'),('kg'),('fft');

insert into travaux (id_maison,id_travaux,id_unite,quantite,duree) values 
(1,2,1,18.4,90),
(1,3,3,781,90),
(1,4,2,150,90),
(1,5,1,16,90),
(1,6,1,54,90),
(1,7,2,145,90),
(1,8,2,160,90),
(1,9,2,6,90),

(2,1,4,1,120),
(2,2,1,21.16,120),
(2,3,3,820.05,120),
(2,4,2,160.5,120),
(2,5,1,30.4,120),
(2,6,1,91.8,120),
(2,7,2,203,120),
(2,8,2,192,120),
(2,9,2,6.6,120),

(3,1,4,2,75),
(3,2,1,22.08,75),
(3,3,3,851.29,75),
(3,5,1,20.08,75),
(3,6,1,59.4,75),
(3,8,2,304,75),
(3,9,2,10.2,75);

create or replace view v_details_travaux as 
select 
    tpm.type_maison,tpm.description,tpm.surface,
    ctrv.code_travaux,ctrv.type_travaux,
    ctrv.prix_unitaire,
    u.unite,
    trv.quantite,
    trv.duree
from type_maison tpm join travaux trv on tpm.id=trv.id_maison
join code_travaux ctrv on trv.id_travaux=ctrv.id
join unite u on u.id=trv.id_unite where trv.id_maison=1 order by trv.id_maison,trv.id_travaux asc ;