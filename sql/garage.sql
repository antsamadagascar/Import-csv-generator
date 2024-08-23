/* example de test 
            created by Aina Ny Antsa Ratovonandrasana @misaina
*/
create database testImport;
use testImport;

create table type_voiture (
    id int primary key auto_increment,
    type_voiture VARCHAR(20)
);

create table services (
    id int PRIMARY key auto_increment,
    service VARCHAR(20),
    duree int
);

create table clients (
    id int PRIMARY key auto_increment,
    nom_Voiture VARCHAR(20),
    type_voiture_id int,
    first_login TIMESTAMP DEFAULT CURRENT_TIMESTAMP(),
    foreign key (type_voiture_id) references type_voiture(id)

);

create table mouvement_service(
    id int primary key auto_increment,
    id_client int,
    date_rdv DATE,
    heure_rdv time,
    id_service int,
    montant decimal(10,2),
    date_paiement DATE,
    FOREIGN key (id_client) REFERENCES clients (id),
    FOREIGN key (id_service) REFERENCES services (id)
);

-----------view pour voir les resultas du mouvement ----------
create or replace view  v_details_service as  
    select 
        cl.nom_Voiture voiture,
        mvs.date_rdv,
        mvs.heure_rdv,
        s.service type_service,
        mvs.montant,
        mvs.date_paiement
    from mouvement_service as mvs
        join clients cl on mvs.id_client=cl.id 
        join services s on s.id=mvs.id_Service
        order by 
            mvs.date_rdv,
            mvs.heure_rdv,
            mvs.date_paiement;