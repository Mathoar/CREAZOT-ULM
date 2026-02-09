--
-- PostgreSQL database dump
--

-- Dumped from database version 16.4
-- Dumped by pg_dump version 16.4

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

--
-- Data for Name: user; Type: TABLE DATA; Schema: public; Owner: app
--

COPY public."user" (id, email, first_name, last_name, roles, keycloak_id) FROM stdin;
1f000a01-0394-65de-b10d-c39afce9e08d	loustau-pierre@outlook.fr	loustic	Loustau	[]	\N
1f000a03-4784-69e0-85cf-c39afce9e08d	planetair974@gmail.com	luis	OLC	[]	\N
1f000a04-c857-6cc4-a108-c39afce9e08d	sebastien.maillot@coding-academy.fr	hans	Techer	[]	\N
1f000a05-b11b-699c-8cc6-c39afce9e08d	planetair974@icloud.com	boris	Vian	[]	\N
1f000a06-9819-686c-9b8d-c39afce9e08d	olivier.fromentin@gmail.com	fromentin	Fromentin	[]	\N
1f000a07-a2bc-6da4-bcac-c39afce9e08d	hugues.williamson@planetair974.com	hugues	Williamson	[]	\N
1f008640-2de8-6392-8a46-1f3c824a438d	jed@planetair974.com	jed	Jed	[]	\N
1f00971f-f3f5-6d56-b729-1315ef24fbc8	gildas@planetair974.com	gildas	Sitarane	[]	\N
1f011e26-6b9f-604a-a24e-6900d92931ed	francky@planetair974.com	francky	FENDRICH	[]	\N
1f01db88-7afb-6cda-a7dd-515d112c8245	thymotee@planetair974.com	thymotée	Thymotée	[]	\N
1f07dc40-c5c6-6a94-87b3-91827596d66e	charles.leclerc@creazot.com	charles	Leclerc	["ROLE_USER","OIDC_USER","ROLE_ADMIN","OIDC_ADMIN"]	\N
1f07dc36-e29d-68ca-97c8-55dbbc9af676	charles.leclerc@boutikpei.com	charles	Leclerc	["ROLE_USER","OIDC_USER","ROLE_ADMIN","OIDC_ADMIN"]	\N
1f00c5ed-cdd0-69d0-8372-8f7aea458831	roulphy@planetair974.com	roulphy	Roulphy	[]	43517e2c-6a25-4649-91bb-bc828964b023
1f024f8a-8847-61e8-8b16-c90f79762d97	chloe@planetair974.com	chloe	Chloe	[]	58a01572-f4c3-4e2e-9622-f9f8e513002e
1f01ebb8-f485-6bce-81d0-afc6e2cc12c4	sebastien.maillot@gmx.fr	Sébastien	Maillot	["ROLE_USER","OIDC_USER","ROLE_ADMIN","OIDC_ADMIN"]	5887dcb1-88c8-4268-9682-3c2acd4681b0
1f07e30d-2b09-6334-87f0-b7f2de3bd9a6	lando.norris@boutikpei.re	Lando	Norris	["ROLE_USER","OIDC_USER","ROLE_ADMIN","OIDC_ADMIN"]	56bbd380-08d7-4e86-9218-10dd0187e442
1f024e74-ad22-6808-a0cc-9b7b2ff302fc	boris@planetair974.re	boris	vian	["ROLE_USER","OIDC_USER","ROLE_ADMIN","OIDC_ADMIN"]	e9de4f6d-f5c8-422a-902e-841d127fd22d
1f07dcbe-c3eb-6520-b05d-9b2e91069048	charles.leclerc@fraispei.re	Charles	Leclerc	["ROLE_USER","OIDC_USER","ROLE_ADMIN","OIDC_ADMIN"]	2aa71fee-ab4c-45c6-b71d-ea3f66f31ea4
1f08e51e-d4bb-6e5e-ac48-c9cbe02f0cce	xavier.mennessier@planetair974.com	Xavier	Menessier	["ROLE_USER","OIDC_USER"]	46f615a6-0290-4b51-97d6-2d2d8086b2d2
1f08e632-c824-64e2-a414-ab9a9b6fd4ce	guy.fourrageat@gmail.com	Guy	FOURRAGEAT	["ROLE_USER","OIDC_USER"]	885b1569-b935-4c9a-8954-f3a20cd24ddf
1f08e63b-1f0a-6f7e-8c6c-19b4a2eaa76f	paul@planetair974.com	Paul	Fauré	["ROLE_USER","OIDC_USER"]	a9bd18ab-cea4-43bc-a486-c130a0a15064
1f08e648-d1b0-60ae-8ffc-1328acbf9d90	jj@planetair974.com	Jean-Jacques	Giordan	["ROLE_USER","OIDC_USER"]	3457850f-70ed-4b92-ad13-aa7f4c10b675
1f08e656-c2c6-6c4c-b5be-c78fe5e67788	hans@planetair974.com	Hans	Techer	["ROLE_USER","OIDC_USER"]	525f8c97-fb8d-4103-a619-d7212a325867
1effe8dc-f73d-600a-bd2c-3324ddd39a2e	sebastien351@hotmail.com	Sébastien	Maillot	["ROLE_USER","OIDC_USER","ROLE_ADMIN","OIDC_ADMIN"]	221c1527-bf5a-4790-a523-642c7ef41f2c
\.


--
-- Data for Name: aeronef; Type: TABLE DATA; Schema: public; Owner: app
--

COPY public.aeronef (id, immatriculation, horametre, entretien, "decimal", seuil_alerte, alerte_envoyee, changement_moteur, seuil_alerte_changement_moteur, alerte_moteur_envoyee, code_balise, created_at, updated_at, created_by_id, updated_by_id, is_available) FROM stdin;
2	F-JDQJ	4208.54	4200	f	10	f	6000	100	\N	20635F0108000711	\N	2025-09-01 16:27:37	\N	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4	t
1	F-JCTX	4183.1	4200	t	10	f	6000	100	\N	20635F0108000C0D	\N	2025-09-10 06:34:08	\N	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4	t
5	F-JINS	17.44	100	f	10	f	2000	200	f	20635F0108000DA5	\N	2025-09-11 08:56:37	\N	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4	f
6	F-JZPT	4181.9	4300	f	10	\N	6000	200	\N	\N	2025-08-31 18:01:15	2025-09-11 08:56:41	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4	f
3	F-JIXJ	2204.09	2200	f	10	t	4000	200	f	20635F0108000711	\N	2025-09-11 15:07:25	\N	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4	t
4	F-JLMT	267.32	300	f	10	f	2000	200	f	20635F0108000C2C	\N	2025-09-11 15:07:26	\N	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4	t
\.


--
-- Data for Name: client; Type: TABLE DATA; Schema: public; Owner: app
--

-- COPY public.client (id, name, slug, email, phone, logo, favicon, pdf_background, map_icon, color, lat, lng, zoom, cam_ids, airport_codes, opacity, active, created_at, updated_at, timezone, address, zipcode, city, thanks_image, website, has_passenger_registration, has_options, has_partners, has_gifts, thanks_title, thanks_message, has_reservation, has_origin_contact, has_landing_management, has_email_confirmation, email_server, confirmation_message, email_address_sender, confirmation_subject, has_payment_management, url, has_microtrak_tag, has_webshop, seuil_medical, seuil_qualifications, has_individual_flight_logs, use_availability_filter, consent_text, has_expenses_management, min_hours, max_hours, has_group_update) FROM stdin;
-- 2	Planetair974	\N	planetair974@gmail.com	+262 692 61 92 04	/images/client/logo.png	/images/favicon.ico	/images/client/Plane.png	/images/client/FlightIcon.png	#d32f2f	-21.1351	55.5114	9	[{"id":"1573961746","nom":"Piton Basalte"},{"id":"1553393899","nom":"Piton Partage"},{"id":"1288200658","nom":"Bory"},{"id":"1266314180","nom":"Piton de Bert"},{"id":"1674820139","nom":"Sainte-Rose"},{"id":"1349899813","nom":"Plaine des Palmistes"},{"id":"1672453852","nom":"Salazie - Trou de fer"},{"id":"1505780894","nom":"Salazie"},{"id":"1643459595","nom":"Bois Court"},{"id":"1451544234","nom":"Saint-Pierre"},{"id":"1611921907","nom":"Le Port"},{"id":"1429566170","nom":"La Saline les bains"}]	[{"code":"FMEP","name":"Pierrefonds","nom":"Pierrefonds","meteo":true,"main":true},{"code":"FMEE","name":"Roland-Garros","nom":"Roland-Garros","meteo":true,"main":false},{"code":"LFMA","name":null,"nom":"Aix les Milles","meteo":false,"main":false}]	0.7	t	2025-05-12 17:31:06	2025-09-18 17:54:56	Indian/Reunion	9 allée des champignons	97450	Saint-Louis	/images/client/Thanks.png	https://www.planetair974.fr	t	t	t	t	FORMULAIRE D'ENREGISTREMENT	<p style="text-align: center"><span style="color: rgb(245, 245, 245)">.<br>.</span></p><h1 style="text-align: center"><span style="color: rgb(211, 47, 47)"><strong>Merci {{FIRSTNAME}} !</strong></span></h1><p style="text-align: center">Bon vol à vous avec<span style="color: #212121"> </span><a target="_blank" rel="noopener noreferrer nofollow" href="https://admin.planetair974.re"><span style="color: #212121">Planetair974</span></a><span style="color: #212121">.</span><br><br><em>Profitez pleinement du moment, nous nous occupons de </em><a target="_blank" rel="noopener noreferrer" class="text-blue-500" href="https://drive.google.com/drive/u/3/folders/0BzOWcOCzePzTSzRLTmFVMDFPOHM?resourcekey=0-iLj4-YqX5cggqazBJQxd7A"><em>vos souvenirs de l'Île intense vue du ciel</em></a><em>.</em><br><img src="https://localhost/images/thanks.png"><span style="color: rgb(75, 85, 99)">Votre vol vous a plu ?</span><br><span style="color: rgb(75, 85, 99)">Faites-le savoir sur </span><a target="_blank" rel="noopener noreferrer" class="text-blue-500" href="https://www.tripadvisor.fr/Attraction_Review-g298471-d3558149-Reviews-Planetair974-Saint_Pierre_Arrondissement_of_Saint_Pierre.html">TripAdvisor</a><span style="color: rgb(75, 85, 99)"> et sur </span><a target="_blank" rel="noopener noreferrer" class="text-blue-500" href="https://www.google.com/maps/place/Planetair974/@-21.3190806,55.4261019,17z/data=!3m1!4b1!4m6!3m5!1s0x2182a13f4362468b:0x725ef4e733adb25d!8m2!3d-21.3190806!4d55.4261019!16s%2Fg%2F1ptwhwyvy?entry=ttu&amp;g_ep=EgoyMDI1MDMwOC4wIKXMDSoASAFQAw%3D%3D">Google</a><span style="color: rgb(75, 85, 99)">!</span><br></p>	t	t	t	t	mailjet+api://aea85528ecc2cc00762bdeeeb7204700:ade808cb62b371a1490f64ee7eb9a728@api.mailjet.com	<p><strong>Bonjour {{FIRSTNAME}},</strong></p><p></p><p>Merci encore pour votre vol avec nous !<br>🎥 📸 Voici le lien vers notre banque d’images, où vous trouverez les<strong> </strong><a target="_blank" rel="noopener noreferrer" class="text-blue-500" href="https://drive.google.com/drive/u/3/folders/0BzOWcOCzePzTSzRLTmFVMDFPOHM?resourcekey=0-iLj4-YqX5cggqazBJQxd7A"><strong><em>Photos &amp; vidéos</em></strong><em> </em></a>de votre expérience dans le ciel de <strong>l’île intense</strong> 🌋🌴🏖</p><p>Vous avez aimé ce moment ? 🤩<br>Un petit avis sur <a target="_blank" rel="noopener noreferrer" class="text-blue-500" href="https://www.tripadvisor.fr/Attraction_Review-g298471-d3558149-Reviews-Planetair974-Saint_Pierre_Arrondissement_of_Saint_Pierre.html"><strong>TripAdvisor</strong></a> ou <a target="_blank" rel="noopener noreferrer" class="text-blue-500" href="https://www.google.com/maps/place/Planetair974/@-21.3190806,55.4261019,17z/data=!3m1!4b1!4m6!3m5!1s0x2182a13f4362468b:0x725ef4e733adb25d!8m2!3d-21.3190806!4d55.4261019!16s%2Fg%2F1ptwhwyvy?entry=ttu&amp;g_ep=EgoyMDI1MDMwOC4wIKXMDSoASAFQAw%3D%3D"><strong>Google</strong></a> serait la plus belle des récompenses pour notre équipe 👨‍✈️💙<br>Merci d’avance pour votre soutien 🙏<strong>🙏</strong></p><p></p><p></p><p><img src="https://localhost/images/logo.png"><strong>PLANETAIR974</strong> 🇷🇪<br>📞 +262 (0)6 92 61 92 04<br>📬 <a target="_blank" rel="noopener noreferrer nofollow" href="mailto:planetair974@gmail.com"><span style="color: rgb(33, 150, 243)">Nous écrire</span></a> -<a target="_blank" rel="noopener noreferrer nofollow" href="http://Planetair974.fr"> </a>🌐 <a target="_blank" rel="noopener noreferrer nofollow" class="hover:underline" href="https://planetair974.fr/"><span style="color: rgb(33, 150, 243)">Planetair974.fr</span></a></p>	contact@planetair974.com	Vos souvenirs de l’île intense vous attendent 📸✨	t	https://localhost	t	t	90	90	t	t	Je reconnais avoir pris connaissance du fait que les exigences applicables aux vols en ULM ne garantissent pas un niveau de sécurité aussi élevé que les vols commerciaux de l'aviation certifiée.\nL'ULM, le pilote et l'exploitant ne sont pas soumis à des opérations de contrôle préalables de la part de l'autorité. 	t	02:00:10	16:00:13	t
-- \.


--
-- Data for Name: airport; Type: TABLE DATA; Schema: public; Owner: app
--

COPY public.airport (id, code, name, main, meteo, client_id) FROM stdin;
2	FMEE	Roland-Garros	f	t	2
1	FMEP	Pierrefonds	t	t	2
4	LFML	Marignane	f	t	2
5	\N	Bras-Panon	f	f	2
3	LFMA	Aix Les Milles	f	t	2
6	RE-0008	Cambaie	f	t	2
7	FMEP	Pierrefonds	t	t	2
8	FMEE	Roland-Garros	f	t	2
9	LFMA		f	f	2
\.


--
-- Data for Name: nature; Type: TABLE DATA; Schema: public; Owner: app
--

COPY public.nature (id, code, label) FROM stdin;
1	VLO	Vol Local à titre Onéreux
2	VSO	Vol à Sensation à titre Onéreux
3	VEF	Vol d’Entraînement ou de Formation
4	VAPO	Vol pour Activité Particulière à titre Onéreux
5	AUTRE	Autre
6	N/A	Non Applicable
\.


--
-- Data for Name: circuit; Type: TABLE DATA; Schema: public; Owner: app
--

COPY public.circuit (id, nom, code, prix, cout, duree, prix_fixe, avec_options, nature_id, needs_encadrant, require_landing_declaration, had_default_landing, webshop_id) FROM stdin;
5	Instruction	INST	135	45	21:00:00	f	f	3	t	t	f	\N
6	Location	LOC	110	0	21:00:00	f	f	6	f	t	f	\N
7	Entraînement	ENT	0	0	21:00:00	f	f	3	t	t	f	\N
8	Photos	PHOTO	180	40	21:00:00	f	f	4	f	f	t	\N
9	Largage Parachutiste	PARA	90	10	20:20:00	f	f	4	f	f	t	\N
10	Vol de Contrôle	VDC	0	0	20:30:00	f	f	5	f	f	f	\N
12	Vol Adapté	MNE	170	40	21:00:00	f	t	1	f	t	f	\N
1	Intégral	3CVL	210	50	21:15:00	t	t	1	f	f	t	88544c09-f5f2-51c3-2459-83363c1e2088
2	Patrimoine	3CV	170	40	21:00:00	t	t	1	f	f	t	c42200e9-34e7-a56f-410b-fb236940869d
3	Cirques & Cascades	CC	140	33.33	20:50:00	t	t	1	f	f	t	ec8ea1af-e992-2936-3233-4f68490c34f8
4	2 Pitons	2V	130	30	20:45:00	t	t	1	f	f	t	57fe2bcc-4b8e-208e-43dc-f59e43874c4d
11	Initiation	Init	135	45	20:30:00	t	f	3	f	f	t	b2c95f4f-6f46-be53-d13e-097293a734a8
13	Baptême	BAPT	60	13.33	20:20:00	t	t	1	t	f	t	c114f804-bf9e-940c-6e6a-96f425f59ed0
\.


--
-- Data for Name: combinaison; Type: TABLE DATA; Schema: public; Owner: app
--

-- COPY public.combinaison (id, nom, min_passager, prix, options) FROM stdin;
-- 8	2 Portes Photos	2	40	[{"@id":"\\/options\\/1","name":"Porte Photo"},{"@id":"\\/options\\/1","name":"Porte Photo"}]
-- 9	2 Portes Photos Offertes	2	0	[{"@id":"\\/options\\/2","name":"Porte Photo Offerte"},{"@id":"\\/options\\/2","name":"Porte Photo Offerte"}]
-- 10	2 Portes Photos dont 1 offerte	2	20	[{"@id":"\\/options\\/1","name":"Porte Photo"},{"@id":"\\/options\\/2","name":"Porte Photo Offerte"}]
-- 6	Porte Photos	1	20	[{"@id":"\\/options\\/1","name":"Porte Photo"}]
-- 7	Porte Photos Offerte	1	0	[{"@id":"\\/options\\/2","name":"Porte Photo Offerte"}]
-- \.


--
-- Data for Name: option; Type: TABLE DATA; Schema: public; Owner: app
--

-- COPY public.option (id, nom, prix) FROM stdin;
-- 2	Porte Photo Offerte	0
-- 1	Porte Photo	20
-- \.


--
-- Data for Name: cadeau; Type: TABLE DATA; Schema: public; Owner: app
--

-- COPY public.cadeau (id, code, beneficiaire, offreur, fin, payment_id, used, cout, circuit_id, option_id, email, message, send_email, quantite, date, prix, options_id, gift, telephone) FROM stdin;
-- 1	8nr12uhcfbz7m	BOUILLOUX Louise	MAILLOT Sébastien	2026-04-14	\N	t	170	2	\N	sebastien.maillot@gmx.fr	Joyeux anniversaire à toi Loulou !	f	\N	\N	\N	\N	\N	\N
-- 5	tor5b9bpuue	VITRY Cécile	MAILLOT Sébastien	2026-04-21	\N	t	170	2	\N	sebastien.maillot@gmx.fr	Joyeux anniversaire à toi Cécile !	f	\N	\N	\N	\N	\N	\N
-- 9	av9iowx8sdrvp	Albert DUFOSSE	Albert DUFOSSE	2026-08-01	10093	t	\N	2	\N	sebastien.maillot@gmx.fr	\N	f	3	2025-07-31	479	6	f	0692406298
-- 7	axw0ym2n7zhz	Sébastien MAILLOT	Sébastien MAILLOT	2026-07-30	29289	t	\N	1	\N	\N	\N	f	3	2025-07-29	587	6	f	\N
-- 18	jfxhhs7swzrt7	Dany X le Hardy	Dany X le Hardy	2026-09-06	10093	t	\N	2	\N	sebastien.maillot@gmx.fr	\N	f	1	2025-09-05	173	6	f	0692406298
-- 10	j5ezauytqwcrn	Antoine DUPONT	Antoine DUPONT	2026-08-02	10098	t	\N	2	\N	sebastien.maillot@gmx.fr	\N	f	2	2025-08-01	326	6	f	0692406298
-- 11	swrwjzdpz4pqq	Sébastien Chabal	Serge RAMOS	2026-08-02	10099	t	\N	1	\N	sebastien.maillot@gmx.fr	\N	f	1	2025-08-01	210	\N	t	0692406298
-- 19	v1bx8rrurorm	Marcus Gronholm	Chase Murray	2026-09-06	10094	t	\N	2	\N	sebastien.maillot@gmx.fr	\N	f	1	2025-09-05	173	6	t	0692406298
-- 8	ccghqtn6lheje	Beneficiaire	Offrant	2026-07-31	10092	t	\N	2	\N	sebastien.maillot@gmx.fr	\N	f	1	2025-07-30	153	\N	t	\N
-- 29	eylcsuygwr5p	Fernando ALONSO	Fernando ALONSO	2026-09-15	10104	t	\N	2	\N	sebastien.maillot@gmx.fr	\N	f	1	2025-09-14	173	6	f	0692 40 62 98
-- 20	jobnrvv3zcmvb	Stella Grondin	Stella Grondin	2026-09-06	10095	t	\N	1	\N	sebastien.maillot@gmx.fr	\N	f	2	2025-09-05	420	\N	f	0692406298
-- 13	34fuurvtmj29h	Sébastien WADOUX	Sébastien MAILLOT	2026-08-08	10090	t	\N	1	\N	sebastien.maillot@gmx.fr	\N	f	1	2025-08-07	230	6	t	0692406298
-- 23	3x5b3p6rs86xl	Thierry HENRI	Thierry HENRI	2026-09-15	\N	t	\N	1	\N	sebastien.maillot@gmx.fr	\N	f	1	2025-09-14	209	6	f	0692 40 62 98
-- 3	bjudqtbdl5kg	Hervé LEONARD	Jules DELAHAY	2026-04-21	\N	t	170	2	\N	sebastien.maillot@gmx.fr	Joyeux anniversaire Hervé	f	1	\N	\N	\N	\N	\N
-- 6	7zco46siym0r4	Sébastien MAILLOT	Sébastien MAILLOT	2026-07-30	29289	t	\N	2	\N	sebastien.maillot@gmx.fr	JOYEUX ANNIVERSAIIIRE !	f	1	2025-07-29	190	6	f	\N
-- 16	t1cpwspbhuxv	Didier HUDRY	Didier HUDRY	2026-08-21	10091	t	\N	1	\N	didier.hudry@gmail.com	\N	f	2	2025-08-20	420	\N	f	0674983241
-- 17	t1cpz914ws6y	Didier HUDRY	Didier HUDRY	2026-08-21	10092	t	\N	1	\N	didier.hudry@gmail.com	\N	f	1	2025-08-20	230	6	f	0674983241
-- 25	yg64lddofmwzb	Pierre GASLY	Pierre GASLY	2026-09-15	10101	t	\N	1	\N	sebastien.maillot@gmx.fr	\N	f	1	2025-09-14	189	7	f	0692 40 62 98
-- 4	j0f4fnfsctir	Amina LALA	Pierre LOUSTAU	2026-04-21	Interne	t	170	2	\N	sebastien.maillot@gmx.fr	Cadeau de fin d'année	f	1	\N	\N	\N	\N	\N
-- 12	ootbnl78meyk	Louise BOUILLOUX	Sébastien Maillot	2026-08-08	10089	t	\N	2	\N	sebastien.maillot@gmx.fr	\N	f	1	2025-08-07	173	6	t	0692406298
-- 28	6jqyssno7ya6	Gabriel BARTOLETTO	Gabriel BARTOLETTO	2026-09-15	\N	t	\N	2	\N	sebastien.maillot@gmx.fr	\N	f	1	2025-09-14	173	6	f	0692 40 62 98
-- 2	h063tbcwgood	Sébastien MAILLOT	Pierre LOUSTAU	2026-04-21	26791	t	210	1	2	sebastien.maillot@gmx.fr	Merci pour ce site magnifique	f	1	\N	\N	\N	\N	\N
-- 21	1eqnqx1p2f9w	Jean-Pierre PAPIN	Jean-Pierre PAPIN	2026-09-15	10098	t	\N	1	\N	sebastien.maillot@gmx.fr	\N	f	1	2025-09-14	230	6	f	0692 40 62 98
-- 27	5usqqp69zqgw	Yuki TSUNODA	Yuki TSUNODA	2026-09-15	10103	t	\N	2	\N	sebastien.maillot@gmx.fr	\N	f	1	2025-09-14	190	6	f	0692 40 62 98
-- 24	lms26rwyou53	Helmut Marko	Helmut Marko	2026-09-15	10100	t	\N	2	\N	sebastien.maillot@gmx.fr	\N	f	2	2025-09-14	326	6	f	0692 40 62 98
-- 26	w75ygj9pfbexc	Esteban OCON	Esteban OCON	2026-09-15	10102	t	\N	2	\N	sebastien.maillot@gmx.fr	\N	f	2	2025-09-14	326	6	f	0692 40 62 98
-- \.


--
-- Data for Name: origine; Type: TABLE DATA; Schema: public; Owner: app
--

-- COPY public.origine (id, name, discount, has_commission) FROM stdin;
-- 1	Le Routard	0	f
-- 2	Wein	0	f
-- 3	Réceptif	0	f
-- 4	Flyer	0	f
-- 5	Bouche à oreille	0	f
-- 7	Web	0	f
-- 8	Air France	10	f
-- 9	La Saga du Rhum	10	f
-- 10	Autre	0	f
-- 6	Office du Tourisme	0	t
-- \.


--
-- Data for Name: cadeau_origine; Type: TABLE DATA; Schema: public; Owner: app
--

-- COPY public.cadeau_origine (cadeau_id, origine_id) FROM stdin;
-- 1	10
-- 2	5
-- 3	7
-- 4	5
-- 7	9
-- 8	8
-- 9	8
-- 10	9
-- 11	7
-- 12	7
-- 12	9
-- 13	5
-- 16	7
-- 17	7
-- 18	8
-- 19	8
-- 20	7
-- 21	6
-- 21	5
-- 23	7
-- 23	9
-- 24	9
-- 24	7
-- 24	5
-- 25	8
-- 25	7
-- 26	9
-- 26	7
-- 27	7
-- 28	4
-- 29	7
-- 29	9
-- 28	9
-- \.


--
-- Data for Name: camera; Type: TABLE DATA; Schema: public; Owner: app
--

-- COPY public.camera (id, code, nom, client_id) FROM stdin;
-- 2	1573961746	Piton Basalte	2
-- 3	1553393899	Piton Partage	2
-- 4	1288200658	Bory	2
-- 5	1266314180	Piton de Bert	2
-- 6	1674820139	Sainte-Rose	2
-- 7	1349899813	Plaine des Palmistes	2
-- 8	1672453852	Salazie - Trou de fer	2
-- 9	1505780894	Salazie	2
-- 10	1643459595	Bois Court	2
-- 11	1451544234	Saint-Pierre	2
-- 12	1611921907	Le Port	2
-- 13	1429566170	La Saline les bains	2
-- 14	1573961746	Piton Basalte	2
-- 15	1553393899	Piton Partage	2
-- 16	1288200658	Bory	2
-- 17	1266314180	Piton de Bert	2
-- 18	1674820139	Sainte-Rose	2
-- 19	1349899813	Plaine des Palmistes	2
-- 20	1672453852	Salazie - Trou de fer	2
-- 21	1505780894	Salazie	2
-- 22	1643459595	Bois Court	2
-- 23	1451544234	Saint-Pierre	2
-- 24	1611921907	Le Port	2
-- 25	1429566170	La Saline les bains	2
-- \.


--
-- Data for Name: profil_pilote; Type: TABLE DATA; Schema: public; Owner: app
--

COPY public.profil_pilote (id, pilote_id, birth_date, total_flight_hours, created_at, updated_at, created_by_id, updated_by_id, available_by_default) FROM stdin;
11	1f000a04-c857-6cc4-a108-c39afce9e08d	2025-01-01 00:00:00	0	\N	2025-09-03 18:14:07	\N	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4	f
27	1f024e74-ad22-6808-a0cc-9b7b2ff302fc	1982-05-25 00:00:00	3.7	\N	2025-09-03 18:14:19	\N	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4	f
34	1f07e30d-2b09-6334-87f0-b7f2de3bd9a6	2025-08-31 12:22:26	0	2025-08-31 12:23:02	2025-09-03 18:14:35	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4	f
16	1f00971f-f3f5-6d56-b729-1315ef24fbc8	2025-01-01 00:00:00	5.68	\N	2025-09-03 18:50:18	\N	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4	f
42	1f08e632-c824-64e2-a414-ab9a9b6fd4ce	2025-01-01 00:00:00	0	2025-09-10 16:28:24	\N	1f08e632-c824-64e2-a414-ab9a9b6fd4ce	\N	t
44	1f08e648-d1b0-60ae-8ffc-1328acbf9d90	2025-01-01 00:00:00	0	2025-09-10 16:38:16	\N	1f08e648-d1b0-60ae-8ffc-1328acbf9d90	\N	t
45	1f08e656-c2c6-6c4c-b5be-c78fe5e67788	2025-01-01 00:00:00	0	2025-09-10 16:44:30	\N	1f08e656-c2c6-6c4c-b5be-c78fe5e67788	\N	t
43	1f08e63b-1f0a-6f7e-8c6c-19b4a2eaa76f	2025-01-01 00:00:00	0	2025-09-10 16:32:08	2025-09-10 16:57:33	1f08e63b-1f0a-6f7e-8c6c-19b4a2eaa76f	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4	t
38	1f07dcbe-c3eb-6520-b05d-9b2e91069048	2025-01-01 00:00:00	0	2025-09-10 05:56:47	2025-09-11 12:01:20	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4	f
40	1f07dc40-c5c6-6a94-87b3-91827596d66e	2025-01-01 00:00:00	0	2025-09-10 06:36:46	2025-09-11 12:01:29	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4	f
41	1f08e51e-d4bb-6e5e-ac48-c9cbe02f0cce	1982-06-01 00:00:00	3.25	2025-09-10 14:26:33	2025-09-14 13:14:00	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4	t
8	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	1987-12-03 00:00:00	126.2	\N	2025-09-05 15:34:40	\N	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4	t
15	1f008640-2de8-6392-8a46-1f3c824a438d	2025-01-01 00:00:00	1.7	\N	\N	\N	\N	t
18	1f011e26-6b9f-604a-a24e-6900d92931ed	2025-01-01 00:00:00	2.47	\N	\N	\N	\N	t
26	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4	1987-12-03 00:00:00	47.89	\N	2025-09-03 18:14:13	\N	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4	f
10	1f000a03-4784-69e0-85cf-c39afce9e08d	1969-06-14 00:00:00	41.52	\N	\N	\N	\N	t
12	1f000a05-b11b-699c-8cc6-c39afce9e08d	2025-01-01 00:00:00	26.03	\N	\N	\N	\N	t
13	1f000a06-9819-686c-9b8d-c39afce9e08d	2025-01-01 00:00:00	1	\N	\N	\N	\N	t
14	1f000a07-a2bc-6da4-bcac-c39afce9e08d	2025-01-01 00:00:00	2.17	\N	\N	\N	\N	t
19	1f01db88-7afb-6cda-a7dd-515d112c8245	2025-01-01 00:00:00	2.5	\N	\N	\N	\N	t
25	1f07dc36-e29d-68ca-97c8-55dbbc9af676	2025-08-20 12:47:31	0	\N	\N	\N	\N	t
17	1f00c5ed-cdd0-69d0-8372-8f7aea458831	2025-01-01 00:00:00	1.8	\N	\N	\N	\N	t
23	1f024f8a-8847-61e8-8b16-c90f79762d97	2025-01-01 00:00:00	0	\N	\N	\N	\N	t
9	1f000a01-0394-65de-b10d-c39afce9e08d	1968-07-13 00:00:00	13.59	\N	\N	\N	\N	t
\.


--
-- Data for Name: carnet_vol; Type: TABLE DATA; Schema: public; Owner: app
--

COPY public.carnet_vol (id, date, aeronef, duree, lieu_depart, is_validated, created_at, updated_at, profil_id, created_by_id, updated_by_id, lieux_arrivee, type_de_vol_id) FROM stdin;
203	2025-03-13 05:12:13	F-JCTX	1	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	8	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	\N	["FMEP - Pierrefonds"]	1
204	2025-03-14 04:10:14	F-JCTX	1	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	8	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	\N	["FMEP - Pierrefonds"]	1
205	2025-03-16 07:01:11	F-JIXJ	2.06	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	8	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	\N	["FMEP - Pierrefonds"]	1
206	2025-03-16 07:01:11	F-JIXJ	0.87	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	8	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	\N	["FMEP - Pierrefonds"]	1
207	2025-03-20 15:41:16	F-JIXJ	1.27	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	8	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	\N	["FMEP - Pierrefonds"]	1
208	2025-03-20 15:41:16	F-JIXJ	1.03	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	8	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	\N	["FMEP - Pierrefonds"]	1
209	2025-03-21 06:06:47	F-JIXJ	1.05	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	8	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	\N	["FMEP - Pierrefonds"]	1
210	2025-03-24 06:23:39	F-JLMT	0.93	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	8	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	\N	["FMEP - Pierrefonds"]	1
211	2025-04-05 05:57:58	F-JDQJ	2.47	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	8	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	\N	["FMEP - Pierrefonds"]	1
212	2025-04-07 05:46:47	F-JDQJ	1.26	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	8	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	\N	["FMEP - Pierrefonds"]	1
213	2025-04-07 05:46:47	F-JDQJ	1.01	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	8	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	\N	["FMEP - Pierrefonds"]	1
214	2025-03-25 05:22:08	F-JDQJ	0.97	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	8	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	\N	["FMEP - Pierrefonds"]	1
215	2025-03-26 04:19:58	F-JDQJ	1.27	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	8	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	\N	["FMEP - Pierrefonds"]	1
216	2025-03-28 06:39:13	F-JDQJ	1.27	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	8	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	\N	["FMEP - Pierrefonds"]	1
217	2025-03-28 06:39:13	F-JDQJ	1.02	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	8	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	\N	["FMEP - Pierrefonds"]	1
218	2025-03-28 06:39:13	F-JDQJ	0.86	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	8	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	\N	["FMEP - Pierrefonds"]	1
219	2025-03-29 05:32:17	F-JDQJ	1.13	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	8	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	\N	["FMEP - Pierrefonds"]	3
220	2025-03-29 05:32:17	F-JDQJ	1.08	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	8	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	\N	["FMEP - Pierrefonds"]	1
221	2025-03-30 04:20:50	F-JDQJ	1.28	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	8	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	\N	["FMEP - Pierrefonds"]	1
222	2025-04-02 08:01:05	F-JDQJ	0.2	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	8	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	\N	["FMEP - Pierrefonds"]	5
223	2025-04-03 05:54:48	F-JDQJ	2.45	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	8	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	\N	["FMEP - Pierrefonds"]	1
224	2025-04-09 07:15:15	F-JIXJ	1.29	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	8	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	\N	["FMEP - Pierrefonds"]	1
225	2025-04-09 07:15:15	F-JIXJ	1.04	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	8	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	\N	["FMEP - Pierrefonds"]	1
226	2025-04-09 07:15:15	F-JIXJ	1.29	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	8	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	\N	["FMEP - Pierrefonds"]	1
227	2025-04-10 04:27:28	F-JIXJ	1.25	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	8	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	\N	["FMEP - Pierrefonds"]	6
228	2025-04-11 07:29:44	F-JIXJ	2.12	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	8	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	\N	["FMEP - Pierrefonds"]	1
229	2025-04-11 07:29:44	F-JIXJ	0.81	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	8	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	\N	["FMEP - Pierrefonds"]	1
230	2025-04-14 06:41:04	F-JIXJ	2.56	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	8	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	\N	["FMEP - Pierrefonds"]	1
231	2025-04-14 06:41:04	F-JIXJ	0.86	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	8	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	\N	["FMEP - Pierrefonds"]	1
232	2025-04-16 06:45:22	F-JLMT	3.1	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	8	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	\N	["FMEP - Pierrefonds"]	1
233	2025-04-18 06:34:31	F-JDQJ	1.33	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	8	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	\N	["FMEP - Pierrefonds"]	1
234	2025-04-18 06:34:31	F-JDQJ	0.84	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	8	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	\N	["FMEP - Pierrefonds"]	1
235	2025-04-19 06:53:04	F-JCTX	1.28	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	8	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	\N	["FMEP - Pierrefonds"]	1
236	2025-04-19 06:53:04	F-JCTX	1.03	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	8	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	\N	["FMEP - Pierrefonds"]	1
237	2025-04-19 06:53:04	F-JCTX	0.79	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	8	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	\N	["FMEP - Pierrefonds"]	1
238	2025-04-01 08:59:21	F-JCTX	3.9	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	8	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	\N	["FMEP - Pierrefonds"]	1
239	2025-04-01 08:00:00	F-JCTX	0.5	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	8	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	\N	["FMEP - Pierrefonds"]	1
240	2025-04-22 08:00:00	F-JCTX	2.1	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	8	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	\N	["FMEP - Pierrefonds"]	1
241	2025-05-28 08:00:00	F-JCTX	1.3	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	8	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	\N	["FMEP - Pierrefonds"]	1
242	2025-08-13 08:00:00	F-JDQJ	4.05	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	8	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	\N	["FMEP - Pierrefonds"]	1
243	2025-05-28 08:00:00	F-JCTX	1.3	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	8	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	\N	["FMEP - Pierrefonds"]	1
244	2025-05-28 08:00:00	F-JLMT	1.28	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	8	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	\N	["FMEP - Pierrefonds","FMEE - Roland-Garros"]	1
245	2025-05-28 08:00:00	F-JCTX	1.4	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	8	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	\N	["FMEP - Pierrefonds","FMEE - Roland-Garros"]	3
246	2025-05-28 08:00:00	F-JDQJ	1	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	8	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	\N	["FMEP - Pierrefonds"]	1
247	2025-05-28 08:00:00	F-JDQJ	0.83	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	8	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	\N	["FMEP - Pierrefonds"]	1
248	2025-06-03 08:00:00	F-JCTX	0.3	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	8	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	\N	["FMEP - Pierrefonds"]	5
249	2025-06-03 08:00:00	F-JIXJ	1	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	8	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	\N	["FMEP - Pierrefonds"]	1
250	2025-06-03 08:00:00	F-JIXJ	1.25	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	8	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	\N	["FMEP - Pierrefonds"]	1
251	2025-06-08 08:00:00	F-JCTX	1.4	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	8	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	\N	["FMEP - Pierrefonds"]	1
252	2025-06-08 08:00:00	F-JCTX	1.4	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	8	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	\N	["FMEP - Pierrefonds"]	1
253	2025-06-08 08:00:00	F-JCTX	1.1	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	8	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	\N	["FMEP - Pierrefonds"]	1
254	2025-06-08 08:00:00	F-JDQJ	1.3	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	8	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	\N	["FMEP - Pierrefonds"]	1
255	2025-06-08 08:00:00	F-JDQJ	1.03	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	8	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	\N	["FMEP - Pierrefonds"]	1
256	2025-08-11 08:00:00	F-JCTX	1	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	8	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	\N	["FMEP - Pierrefonds"]	1
257	2025-08-11 08:00:00	F-JCTX	2.5	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	8	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	\N	["FMEP - Pierrefonds"]	1
258	2025-08-20 08:00:00	F-JDQJ	1.25	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	8	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	\N	["FMEP - Pierrefonds"]	3
259	2025-08-22 08:00:00	F-JDQJ	2	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	8	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	\N	["FMEP - Pierrefonds"]	1
260	2025-08-23 08:00:00	F-JCTX	2.46	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	8	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	\N	["FMEP - Pierrefonds"]	1
261	2025-08-23 08:00:00	F-JCTX	1.24	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	8	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	\N	["FMEP - Pierrefonds"]	1
262	2025-08-22 08:00:00	F-JLMT	2.5	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	8	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	\N	["FMEP - Pierrefonds"]	1
263	2025-08-22 08:00:00	F-JLMT	1	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	8	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	\N	["FMEP - Pierrefonds"]	1
264	2025-08-22 08:00:00	F-JDQJ	2	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	8	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	\N	["FMEP - Pierrefonds"]	1
265	2025-08-22 08:00:00	F-JDQJ	1.67	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	8	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	\N	["FMEP - Pierrefonds"]	1
266	2025-08-18 08:00:00	F-JDQJ	2.54	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	8	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	\N	["FMEP - Pierrefonds"]	1
267	2025-08-18 08:00:00	F-JDQJ	1.01	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	8	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	\N	["FMEP - Pierrefonds"]	1
268	2025-08-23 08:00:00	F-JLMT	4.2	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	8	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	\N	["FMEP - Pierrefonds"]	1
269	2025-08-23 08:00:00	F-JLMT	1.03	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	8	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	\N	["FMEP - Pierrefonds"]	1
270	2025-08-23 08:00:00	F-JLMT	1.29	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	8	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	\N	["FMEP - Pierrefonds"]	1
271	2025-08-23 08:00:00	F-JCTX	1.3	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	8	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	\N	["FMEP - Pierrefonds"]	1
272	2025-08-24 08:00:00	F-JDQJ	1.27	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	8	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	\N	["FMEP - Pierrefonds"]	1
273	2025-08-24 08:00:00	F-JDQJ	1.03	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	8	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	\N	["FMEP - Pierrefonds"]	1
274	2025-08-24 08:00:00	F-JDQJ	1.27	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	8	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	\N	["FMEP - Pierrefonds"]	1
275	2025-08-24 08:00:00	F-JDQJ	1.03	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	8	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	\N	["FMEP - Pierrefonds"]	1
276	2025-08-30 08:00:00	F-JIXJ	2.56	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	8	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	\N	["FMEP - Pierrefonds"]	1
277	2025-08-30 08:00:00	F-JIXJ	2.07	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	8	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	\N	["FMEP - Pierrefonds"]	1
278	2025-09-04 08:00:00	F-JDQJ	2.04	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	8	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	\N	["FMEP Pierrefonds"]	1
279	2025-09-04 08:00:00	F-JDQJ	1.28	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	8	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	\N	["FMEP Pierrefonds"]	1
280	2025-09-04 08:00:00	F-JCTX	1.2	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	8	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	\N	["\\/airports\\/5 - Bras-Panon","FMEP Pierrefonds"]	6
281	2025-09-04 08:00:00	F-JLMT	1.27	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	8	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	\N	["FMEP Pierrefonds"]	1
282	2025-09-04 08:00:00	F-JLMT	2.05	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	8	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	\N	["FMEP Pierrefonds"]	1
283	2025-09-05 08:00:00	F-JDQJ	1.23	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	8	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	\N	["FMEP Pierrefonds"]	1
284	2025-09-05 08:00:00	F-JDQJ	0.99	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	8	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	\N	["FMEP Pierrefonds"]	1
285	2025-09-05 08:00:00	F-JDQJ	2.54	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	8	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	\N	["FMEP Pierrefonds"]	1
286	2025-09-05 08:00:00	F-JDQJ	0.84	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	8	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	\N	["FMEP Pierrefonds"]	1
287	2025-09-11 08:00:00	F-JDQJ	3.03	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	8	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	\N	["FMEP Pierrefonds"]	1
288	2025-03-16 06:59:59	F-JCTX	2.04	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	9	1f000a01-0394-65de-b10d-c39afce9e08d	\N	["FMEP - Pierrefonds"]	1
289	2025-03-16 06:59:59	F-JCTX	0.86	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	9	1f000a01-0394-65de-b10d-c39afce9e08d	\N	["FMEP - Pierrefonds"]	1
290	2025-03-19 20:00:00	F-JLMT	1.05	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	9	1f000a01-0394-65de-b10d-c39afce9e08d	\N	["FMEP - Pierrefonds"]	1
291	2025-03-21 20:00:00	F-JIXJ	0.73	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	9	1f000a01-0394-65de-b10d-c39afce9e08d	\N	["FMEP - Pierrefonds"]	3
292	2025-04-09 06:11:49	F-JDQJ	1.25	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	9	1f000a01-0394-65de-b10d-c39afce9e08d	\N	["FMEP - Pierrefonds"]	1
293	2025-04-09 06:15:22	F-JDQJ	1.05	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	9	1f000a01-0394-65de-b10d-c39afce9e08d	\N	["FMEP - Pierrefonds"]	3
294	2025-03-31 20:00:00	F-JDQJ	1.03	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	9	1f000a01-0394-65de-b10d-c39afce9e08d	\N	["FMEP - Pierrefonds"]	1
295	2025-04-11 08:12:47	F-JDQJ	1	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	9	1f000a01-0394-65de-b10d-c39afce9e08d	\N	["FMEP - Pierrefonds"]	1
296	2025-04-20 07:15:56	F-JCTX	1.1	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	9	1f000a01-0394-65de-b10d-c39afce9e08d	\N	["FMEP - Pierrefonds"]	1
297	2025-08-24 08:00:00	F-JCTX	0.8	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	9	1f000a01-0394-65de-b10d-c39afce9e08d	\N	["FMEP - Pierrefonds"]	1
298	2025-08-24 08:00:00	F-JCTX	0.8	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	9	1f000a01-0394-65de-b10d-c39afce9e08d	\N	["FMEP - Pierrefonds"]	1
299	2025-08-25 08:00:00	F-JDQJ	1.02	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	9	1f000a01-0394-65de-b10d-c39afce9e08d	\N	["FMEP - Pierrefonds"]	1
300	2025-08-25 08:00:00	F-JDQJ	0.86	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	9	1f000a01-0394-65de-b10d-c39afce9e08d	\N	["FMEP - Pierrefonds"]	1
301	2025-03-19 20:00:00	F-JCTX	1.27	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	10	1f000a03-4784-69e0-85cf-c39afce9e08d	\N	["FMEP - Pierrefonds"]	1
302	2025-03-19 20:00:00	F-JCTX	1.03	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	10	1f000a03-4784-69e0-85cf-c39afce9e08d	\N	["FMEP - Pierrefonds"]	1
303	2025-03-21 20:00:00	F-JDQJ	1.27	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	10	1f000a03-4784-69e0-85cf-c39afce9e08d	\N	["FMEP - Pierrefonds"]	3
304	2025-03-24 06:24:24	F-JDQJ	1.23	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	10	1f000a03-4784-69e0-85cf-c39afce9e08d	\N	["FMEP - Pierrefonds"]	1
305	2025-03-24 06:24:24	F-JDQJ	1.08	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	10	1f000a03-4784-69e0-85cf-c39afce9e08d	\N	["FMEP - Pierrefonds"]	4
306	2025-03-26 05:26:48	F-JIXJ	1.03	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	10	1f000a03-4784-69e0-85cf-c39afce9e08d	\N	["FMEP - Pierrefonds"]	1
307	2025-03-26 05:26:48	F-JIXJ	1.29	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	10	1f000a03-4784-69e0-85cf-c39afce9e08d	\N	["FMEP - Pierrefonds"]	1
308	2025-03-29 05:31:28	F-JLMT	1.13	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	10	1f000a03-4784-69e0-85cf-c39afce9e08d	\N	["FMEP - Pierrefonds"]	3
309	2025-03-27 20:00:00	F-JIXJ	1.08	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	10	1f000a03-4784-69e0-85cf-c39afce9e08d	\N	["FMEP - Pierrefonds"]	1
310	2025-03-27 20:00:00	F-JIXJ	1.08	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	10	1f000a03-4784-69e0-85cf-c39afce9e08d	\N	["FMEP - Pierrefonds"]	1
311	2025-03-29 20:00:00	F-JLMT	1.25	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	10	1f000a03-4784-69e0-85cf-c39afce9e08d	\N	["FMEP - Pierrefonds"]	1
312	2025-03-31 05:53:28	F-JDQJ	0.77	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	10	1f000a03-4784-69e0-85cf-c39afce9e08d	\N	["FMEP - Pierrefonds"]	1
313	2025-03-31 05:53:28	F-JDQJ	0.52	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	10	1f000a03-4784-69e0-85cf-c39afce9e08d	\N	["FMEP - Pierrefonds"]	3
314	2025-03-31 05:53:28	F-JDQJ	1.03	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	10	1f000a03-4784-69e0-85cf-c39afce9e08d	\N	["FMEP - Pierrefonds"]	1
315	2025-04-04 06:58:08	F-JDQJ	1.29	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	10	1f000a03-4784-69e0-85cf-c39afce9e08d	\N	["FMEP - Pierrefonds"]	1
316	2025-04-04 06:58:08	F-JDQJ	2.09	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	10	1f000a03-4784-69e0-85cf-c39afce9e08d	\N	["FMEP - Pierrefonds"]	1
317	2025-04-05 05:49:12	F-JCTX	1.2	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	10	1f000a03-4784-69e0-85cf-c39afce9e08d	\N	["FMEP - Pierrefonds"]	1
318	2025-04-07 00:00:00	F-JLMT	1.25	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	10	1f000a03-4784-69e0-85cf-c39afce9e08d	\N	["FMEP - Pierrefonds"]	1
319	2025-04-07 00:00:00	F-JLMT	1	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	10	1f000a03-4784-69e0-85cf-c39afce9e08d	\N	["FMEP - Pierrefonds"]	1
320	2025-04-09 07:25:37	F-JLMT	2.42	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	10	1f000a03-4784-69e0-85cf-c39afce9e08d	\N	["FMEP - Pierrefonds"]	1
321	2025-04-09 07:25:37	F-JLMT	1.21	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	10	1f000a03-4784-69e0-85cf-c39afce9e08d	\N	["FMEP - Pierrefonds"]	1
322	2025-04-11 07:28:43	F-JLMT	3.73	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	10	1f000a03-4784-69e0-85cf-c39afce9e08d	\N	["FMEP - Pierrefonds"]	1
323	2025-04-15 06:23:51	F-JCTX	3	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	10	1f000a03-4784-69e0-85cf-c39afce9e08d	\N	["FMEP - Pierrefonds"]	1
324	2025-04-13 20:00:00	F-JLMT	1.06	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	10	1f000a03-4784-69e0-85cf-c39afce9e08d	\N	["FMEP - Pierrefonds"]	1
325	2025-04-13 20:00:00	F-JLMT	0.89	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	10	1f000a03-4784-69e0-85cf-c39afce9e08d	\N	["FMEP - Pierrefonds"]	1
326	2025-04-13 20:00:00	F-JLMT	1.3	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	10	1f000a03-4784-69e0-85cf-c39afce9e08d	\N	["FMEP - Pierrefonds"]	1
327	2025-04-16 07:39:10	F-JCTX	3.1	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	10	1f000a03-4784-69e0-85cf-c39afce9e08d	\N	["FMEP - Pierrefonds"]	1
328	2025-04-18 06:40:47	F-JIXJ	0.9	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	10	1f000a03-4784-69e0-85cf-c39afce9e08d	\N	["FMEP - Pierrefonds"]	1
329	2025-04-18 06:40:47	F-JIXJ	0.82	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	10	1f000a03-4784-69e0-85cf-c39afce9e08d	\N	["FMEP - Pierrefonds"]	1
330	2025-08-20 08:00:00	F-JCTX	1.2	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	10	1f000a03-4784-69e0-85cf-c39afce9e08d	\N	["FMEP - Pierrefonds"]	1
331	2025-03-19 20:00:00	F-JDQJ	2.12	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	12	1f000a05-b11b-699c-8cc6-c39afce9e08d	\N	["FMEP - Pierrefonds"]	1
332	2025-03-20 20:00:00	F-JDQJ	1.08	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	12	1f000a05-b11b-699c-8cc6-c39afce9e08d	\N	["FMEP - Pierrefonds"]	1
333	2025-03-24 06:31:24	F-JIXJ	1.16	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	12	1f000a05-b11b-699c-8cc6-c39afce9e08d	\N	["FMEP - Pierrefonds"]	1
334	2025-03-24 06:31:24	F-JIXJ	0.92	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	12	1f000a05-b11b-699c-8cc6-c39afce9e08d	\N	["FMEP - Pierrefonds"]	1
335	2025-03-28 06:40:39	F-JLMT	1.27	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	12	1f000a05-b11b-699c-8cc6-c39afce9e08d	\N	["FMEP - Pierrefonds"]	1
336	2025-03-28 06:40:39	F-JLMT	1.02	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	12	1f000a05-b11b-699c-8cc6-c39afce9e08d	\N	["FMEP - Pierrefonds"]	1
337	2025-03-28 06:40:39	F-JLMT	0.86	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	12	1f000a05-b11b-699c-8cc6-c39afce9e08d	\N	["FMEP - Pierrefonds"]	1
338	2025-03-29 05:38:10	F-JCTX	1.03	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	12	1f000a05-b11b-699c-8cc6-c39afce9e08d	\N	["FMEP - Pierrefonds"]	1
339	2025-03-29 05:38:10	F-JCTX	0.87	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	12	1f000a05-b11b-699c-8cc6-c39afce9e08d	\N	["FMEP - Pierrefonds"]	1
340	2025-03-30 04:40:04	F-JCTX	1.2	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	12	1f000a05-b11b-699c-8cc6-c39afce9e08d	\N	["FMEP - Pierrefonds"]	1
341	2025-04-01 04:20:49	F-JDQJ	1.32	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	12	1f000a05-b11b-699c-8cc6-c39afce9e08d	\N	["FMEP - Pierrefonds"]	1
342	2025-04-11 07:30:05	F-JCTX	2.5	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	12	1f000a05-b11b-699c-8cc6-c39afce9e08d	\N	["FMEP - Pierrefonds"]	1
343	2025-04-11 07:30:05	F-JCTX	1	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	12	1f000a05-b11b-699c-8cc6-c39afce9e08d	\N	["FMEP - Pierrefonds"]	1
344	2025-04-14 11:55:01	F-JCTX	2.5	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	12	1f000a05-b11b-699c-8cc6-c39afce9e08d	\N	["FMEP - Pierrefonds"]	1
345	2025-04-14 11:55:01	F-JCTX	1	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	12	1f000a05-b11b-699c-8cc6-c39afce9e08d	\N	["FMEP - Pierrefonds"]	1
346	2025-04-15 08:29:44	F-JLMT	0	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	12	1f000a05-b11b-699c-8cc6-c39afce9e08d	\N	["FMEP - Pierrefonds"]	1
347	2025-04-18 06:43:33	F-JCTX	1.31	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	12	1f000a05-b11b-699c-8cc6-c39afce9e08d	\N	["FMEP - Pierrefonds"]	1
348	2025-04-18 06:43:33	F-JCTX	0.89	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	12	1f000a05-b11b-699c-8cc6-c39afce9e08d	\N	["FMEP - Pierrefonds"]	1
349	2025-04-18 06:43:33	F-JCTX	0.8	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	12	1f000a05-b11b-699c-8cc6-c39afce9e08d	\N	["FMEP - Pierrefonds"]	1
350	2025-04-19 06:55:52	F-JDQJ	1.31	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	12	1f000a05-b11b-699c-8cc6-c39afce9e08d	\N	["FMEP - Pierrefonds"]	1
351	2025-04-19 06:55:52	F-JDQJ	1.06	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	12	1f000a05-b11b-699c-8cc6-c39afce9e08d	\N	["FMEP - Pierrefonds"]	1
352	2025-04-19 06:55:52	F-JDQJ	0.81	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	12	1f000a05-b11b-699c-8cc6-c39afce9e08d	\N	["FMEP - Pierrefonds"]	1
353	2025-03-14 07:08:09	F-JCTX	1	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	13	1f000a06-9819-686c-9b8d-c39afce9e08d	\N	["FMEP - Pierrefonds"]	3
354	2025-04-12 07:35:29	F-JIXJ	0.9	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	14	1f000a07-a2bc-6da4-bcac-c39afce9e08d	\N	["FMEP - Pierrefonds"]	3
355	2025-04-20 07:16:36	F-JDQJ	1.27	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	14	1f000a07-a2bc-6da4-bcac-c39afce9e08d	\N	["FMEP - Pierrefonds"]	6
356	2025-03-22 20:00:00	F-JDQJ	0.77	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	15	1f008640-2de8-6392-8a46-1f3c824a438d	\N	["FMEP - Pierrefonds"]	6
357	2025-03-31 20:00:00	F-JDQJ	0.93	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	15	1f008640-2de8-6392-8a46-1f3c824a438d	\N	["FMEP - Pierrefonds"]	6
358	2025-03-26 05:26:48	F-JLMT	1.01	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	16	1f00971f-f3f5-6d56-b729-1315ef24fbc8	\N	["FMEP - Pierrefonds"]	1
359	2025-03-26 05:26:48	F-JLMT	1.27	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	16	1f00971f-f3f5-6d56-b729-1315ef24fbc8	\N	["FMEP - Pierrefonds"]	1
360	2025-04-04 06:58:08	F-JCTX	1.3	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	16	1f00971f-f3f5-6d56-b729-1315ef24fbc8	\N	["FMEP - Pierrefonds"]	1
361	2025-04-04 06:58:08	F-JCTX	2.1	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	16	1f00971f-f3f5-6d56-b729-1315ef24fbc8	\N	["FMEP - Pierrefonds"]	1
362	2025-04-05 05:55:04	F-JLMT	0.72	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	18	1f011e26-6b9f-604a-a24e-6900d92931ed	\N	["FMEP - Pierrefonds"]	3
363	2025-04-12 06:17:11	F-JIXJ	0.85	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	18	1f011e26-6b9f-604a-a24e-6900d92931ed	\N	["FMEP - Pierrefonds"]	3
364	2025-08-19 08:00:00	F-JDQJ	0.9	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	18	1f011e26-6b9f-604a-a24e-6900d92931ed	\N	["FMEP - Pierrefonds"]	6
365	2025-04-20 07:53:21	F-JCTX	0.7	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	19	1f01db88-7afb-6cda-a7dd-515d112c8245	\N	["FMEP - Pierrefonds"]	3
366	2025-04-29 08:00:00	F-JCTX	1.1	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	19	1f01db88-7afb-6cda-a7dd-515d112c8245	\N	["FMEE - Roland-Garros","FMEP - Pierrefonds"]	3
367	2025-08-19 08:00:00	F-JCTX	0.7	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	19	1f01db88-7afb-6cda-a7dd-515d112c8245	\N	["FMEP - Pierrefonds"]	3
368	2025-03-29 05:31:28	F-JLMT	0.7	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	17	1f00c5ed-cdd0-69d0-8372-8f7aea458831	\N	["FMEP - Pierrefonds"]	3
369	2025-04-05 05:50:09	F-JCTX	1.1	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	17	1f00c5ed-cdd0-69d0-8372-8f7aea458831	\N	["FMEP - Pierrefonds"]	3
370	2025-08-22 08:00:00	F-JCTX	2.5	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	26	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4	\N	["FMEP - Pierrefonds"]	1
371	2025-08-22 08:00:00	F-JCTX	2.5	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	26	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4	\N	["FMEP - Pierrefonds"]	1
372	2025-08-22 08:00:00	F-JDQJ	5	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	26	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4	\N	["FMEP - Pierrefonds"]	1
373	2025-08-22 08:00:00	F-JLMT	1.25	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	26	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4	\N	["FMEP - Pierrefonds"]	1
374	2025-08-22 08:00:00	F-JLMT	2	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	26	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4	\N	["FMEP - Pierrefonds"]	1
375	2025-08-22 08:00:00	F-JLMT	0.83	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	26	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4	\N	["FMEP - Pierrefonds"]	1
376	2025-08-22 08:00:00	F-JIXJ	2.53	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	26	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4	\N	["FMEP - Pierrefonds"]	1
377	2025-08-22 08:00:00	F-JDQJ	2.54	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	26	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4	\N	["FMEP - Pierrefonds"]	1
378	2025-08-22 08:00:00	F-JDQJ	1.02	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	26	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4	\N	["FMEP - Pierrefonds"]	1
379	2025-08-22 08:00:00	F-JDQJ	0.86	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	26	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4	\N	["FMEP - Pierrefonds"]	1
380	2025-08-23 08:00:00	F-JDQJ	4.05	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	26	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4	\N	["FMEP - Pierrefonds"]	1
381	2025-08-23 08:00:00	F-JDQJ	2.56	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	26	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4	\N	["FMEP - Pierrefonds"]	1
382	2025-08-23 08:00:00	F-JDQJ	1.29	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	26	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4	\N	["FMEP - Pierrefonds"]	1
383	2025-08-23 08:00:00	F-JDQJ	4.2	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	26	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4	\N	["FMEP - Pierrefonds"]	1
384	2025-08-23 08:00:00	F-JLMT	1.27	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	26	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4	\N	["FMEP - Pierrefonds"]	1
385	2025-08-30 08:00:00	F-JDQJ	2.58	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	26	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4	\N	["FMEP - Pierrefonds"]	1
386	2025-08-30 08:00:00	F-JDQJ	2.07	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	26	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4	\N	["FMEP - Pierrefonds"]	1
387	2025-08-30 08:00:00	F-JCTX	1.3	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	26	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4	\N	["FMEP - Pierrefonds"]	1
388	2025-08-30 08:00:00	F-JCTX	0.88	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	26	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4	\N	["FMEP - Pierrefonds"]	1
389	2025-08-30 08:00:00	F-JCTX	2.12	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	26	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4	\N	["FMEP - Pierrefonds"]	1
390	2025-09-05 08:00:00	F-JDQJ	1.23	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	26	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4	\N	["FMEP Pierrefonds"]	1
391	2025-09-05 08:00:00	F-JDQJ	0.99	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	26	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4	\N	["FMEP Pierrefonds"]	1
392	2025-09-12 08:00:00	F-JDQJ	1.28	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	26	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4	\N	["FMEP Pierrefonds"]	1
393	2025-09-12 08:00:00	F-JDQJ	1.04	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	26	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4	\N	["FMEP Pierrefonds"]	1
394	2025-04-29 08:00:00	F-JCTX	1.32	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	27	1f024e74-ad22-6808-a0cc-9b7b2ff302fc	\N	["FMEP - Pierrefonds"]	1
395	2025-04-29 08:00:00	F-JCTX	1.08	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	27	1f024e74-ad22-6808-a0cc-9b7b2ff302fc	\N	["FMEP - Pierrefonds"]	1
396	2025-08-24 08:00:00	F-JCTX	1.3	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	27	1f024e74-ad22-6808-a0cc-9b7b2ff302fc	\N	["FMEP - Pierrefonds"]	1
397	2025-09-14 08:00:00	F-JDQJ	1.25	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	41	1f08e51e-d4bb-6e5e-ac48-c9cbe02f0cce	\N	["FMEP Pierrefonds"]	1
398	2025-09-14 08:00:00	F-JDQJ	2	FMEP - Pierrefonds	t	2026-01-29 15:17:40	\N	41	1f08e51e-d4bb-6e5e-ac48-c9cbe02f0cce	\N	["FMEP Pierrefonds"]	1
\.


--
-- Data for Name: entretien; Type: TABLE DATA; Schema: public; Owner: app
--

COPY public.entretien (id, date, intervention, horametre_intervention, horametre_next_intervention, aeronef_id, changement_moteur, created_at, updated_at, created_by_id, updated_by_id) FROM stdin;
1	2025-03-25	changement cable indicateur pression huile	2178.54	2200	3	f	\N	\N	\N	\N
2	2025-04-02	Vidange, changement du filtre à huile, vérification du bouchon magnétique, changement du disque de frein droit, lubrification des articulations des commandes et vérification de la cellule	4206.23	4300	2	f	\N	\N	\N	\N
4	2025-09-10	Changement de la batterie	4183.1	4200	1	f	2025-09-10 06:38:01	2025-09-10 06:38:15	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4
5	2025-09-12	Vidange et changement du filtre à huile.\nVérification du bouchon magnétique : RAS.\nPoint fixe : RAS.	267.32	300	4	f	2025-09-15 12:02:19	\N	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4	\N
6	2025-09-10	Réglage ralenti.\nEssais : RAS	4180	4200	1	f	2025-09-15 12:07:32	\N	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4	\N
7	2025-09-12	Redressage du ressort de fermeture de porte côté passager.	4200	4200	2	f	2025-09-15 12:10:11	\N	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4	\N
3	2025-04-27	Vidange moteur et changement du filtre à huile. \nVérification du bouchon magnétique : OK\nVérification point fixe : OK	4177.1	4300	1	t	\N	2025-09-16 12:47:11	\N	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4
\.


--
-- Data for Name: media_object; Type: TABLE DATA; Schema: public; Owner: app
--

COPY public.media_object (id, file_path, description, created_at, profil_pilote_id, aeronef_id, airport_id, entretien_id) FROM stdin;
4	bon-cadeau-68b43ea61da18240378538.pdf	Certificat Médical	2025-08-31 12:23:02	\N	\N	\N	\N
5	bon-cadeau-1-68b43ea634bd9249050661.pdf	bon_cadeau (1).pdf	2025-08-31 12:23:02	\N	\N	\N	\N
9	12-mars-68b4838225c65286256067.pdf	12 mars.pdf	2025-08-31 17:16:50	\N	\N	\N	\N
10	17-avril-68b483823cf63441352059.pdf	17 avril.pdf	2025-08-31 17:16:50	\N	\N	\N	\N
11	21-avril-68b4838254e5d541673593.pdf	21 avril.pdf	2025-08-31 17:16:50	\N	\N	\N	\N
8	12-mars-68b47a55942e1100045635.pdf	Attestation	2025-08-31 16:37:41	\N	\N	\N	\N
12	12-mars-68b483e0b83af089534623.pdf	12 mars.pdf	2025-08-31 17:18:24	34	\N	\N	\N
13	17-avril-68b483e0cdc81169417461.pdf	17 avril.pdf	2025-08-31 17:18:24	34	\N	\N	\N
14	21-avril-68b483e0e3038236111804.pdf	21 avril.pdf	2025-08-31 17:18:24	34	\N	\N	\N
15	attestation-de-virement-monsieur-sebastien-maillot-maillot-sebastien-20240805-68b4860d9f7f9379278084.pdf		2025-08-31 17:27:41	\N	\N	\N	\N
16	021000129142119-68b4860dbb3b5634997545.pdf		2025-08-31 17:27:41	\N	\N	\N	\N
25	12-mars-68b48deab9c98251025667.pdf	12 mars.pdf	2025-08-31 18:01:14	\N	6	\N	\N
26	17-avril-68b48dead1dd5704826032.pdf	17 avril.pdf	2025-08-31 18:01:14	\N	6	\N	\N
27	21-avril-68b48deae809e311856240.pdf	21 avril.pdf	2025-08-31 18:01:14	\N	6	\N	\N
28	adhesion-april-68b491fd1424d216173579.pdf	Adhésion APRIL.pdf	2025-08-31 18:18:37	\N	6	\N	\N
29	licence-ulm-68b9d78624e92558324388.pdf	licence ULM.pdf	2025-09-04 18:16:38	\N	\N	\N	\N
30	karrieregrafik-airline-c30af13295-68b9d7e2525fe914581775.pdf	Karrieregrafik_Airline_c30af13295.pdf	2025-09-04 18:18:10	\N	\N	\N	\N
31	cv-68b9d873bff26764662831.pdf	CV.pdf	2025-09-04 18:20:35	\N	\N	\N	\N
33	karrieregrafik-airline-c30af13295-68b9de4c806f5559936525.pdf	Pilote professionnel	2025-09-04 18:45:32	\N	\N	\N	\N
34	diplome-bac-68ba44b151558077724119.pdf	Diplome BAC.pdf	2025-09-05 02:02:25	\N	\N	\N	\N
35	diplome-inge-68ba44b17df4a970780158.pdf	DIPLOME INGE.pdf	2025-09-05 02:02:25	\N	\N	\N	\N
36	diplome-bac-68ba4568ea73d667314700.pdf	Diplome BAC.pdf	2025-09-05 02:05:28	\N	\N	\N	\N
37	diplome-inge-68ba456921eac723259855.pdf	DIPLOME INGE.pdf	2025-09-05 02:05:29	\N	\N	\N	\N
38	diplome-bac-68ba462f701cf374596722.pdf	Diplome BAC.pdf	2025-09-05 02:08:47	\N	\N	\N	3
39	diplome-inge-68ba462f9b501831926951.pdf	DIPLOME INGE.pdf	2025-09-05 02:08:47	\N	\N	\N	3
42	cv-68ba46e0554d9771138689.pdf	Certificat Médical	2025-09-05 02:11:44	\N	\N	\N	\N
43	licence-ulm-68ba46e07104a836378693.pdf	Pilote professionnel	2025-09-05 02:11:44	\N	\N	\N	\N
32	flighticon-68b9dd3a477e7170207398.png	FlightIcon.png	2025-09-04 18:40:58	\N	\N	\N	\N
40	diplome-bac-68ba46e007b0c804860229.pdf	Diplome BAC.pdf	2025-09-05 02:11:44	8	\N	\N	\N
41	diplome-inge-68ba46e03494d320993899.pdf	DIPLOME INGE.pdf	2025-09-05 02:11:44	8	\N	\N	\N
44	admission-form-4551834924-68bec7a79821d560356777.pdf	admission_form_4551834924.pdf	2025-09-08 12:10:15	\N	\N	\N	\N
45	affiliation-and-non-condamnation-1-68bec84814640593945676.pdf	Affiliation & Non condamnation (1).pdf	2025-09-08 12:12:56	\N	\N	\N	\N
47	attestation-68beca9557f77689973540.pdf	Attestation.pdf	2025-09-08 12:22:45	\N	\N	\N	\N
46	bon-cadeau-5-68bec89d78b26669982960.pdf	Sans nom	2025-09-08 12:14:21	\N	\N	\N	\N
48	adhesion-april-68befb0475592944128954.pdf	Adhésion APRIL.pdf	2025-09-08 15:49:24	\N	\N	\N	\N
51	021000129142119-68c11b1818e7c952525531.pdf	Certificat Médical	2025-09-10 06:30:48	\N	\N	\N	\N
52	2021-atelier-68c11b1832593632806000.pdf	Instructeur	2025-09-10 06:30:48	\N	\N	\N	\N
49	affiliation-and-non-condamnation-68c11b17d8554906966465.pdf	Affiliation & Non condamnation.pdf	2025-09-10 06:30:47	38	\N	\N	\N
50	admission-form-4703504628-68c11b17f3a75768164372.pdf	admission_form_4703504628.pdf	2025-09-10 06:30:47	38	\N	\N	\N
53	attestation-de-delivrance-de-l-information-donnee-a-son-conjoint-68c11bdc86c49568609849.pdf	Attestation de délivrance de l’information donnée à son conjoint.pdf	2025-09-10 06:34:04	\N	1	\N	\N
56	12-mars-68c11c7eb4d37062427353.pdf	Certificat Médical	2025-09-10 06:36:46	\N	\N	\N	\N
54	21-avril-68c11c7e7e0da332107365.pdf	21 avril.pdf	2025-09-10 06:36:46	40	\N	\N	\N
55	4225234497-68c11c7e9a221290168964.jpg	4225234497.jpeg	2025-09-10 06:36:46	40	\N	\N	\N
58	bon-cadeau-13-68c11cd50ad72802218685.pdf	bon_cadeau (13).pdf	2025-09-10 06:38:13	\N	\N	\N	4
61	bon-cadeau-28-68c1adf8d7d1e937364109.pdf	Certificat Médical	2025-09-10 16:57:28	\N	\N	\N	\N
62	bon-cadeau-2-68c1adf8f07f1255768909.pdf	Emport Passager	2025-09-10 16:57:28	\N	\N	\N	\N
59	bon-cadeau-7-68c1adf8a39c4797909955.pdf	bon_cadeau (7).pdf	2025-09-10 16:57:28	43	\N	\N	\N
60	bon-cadeau-8-68c1adf8becfb348368246.pdf	bon_cadeau (8).pdf	2025-09-10 16:57:28	43	\N	\N	\N
57	17-avril-68c11c7ece016015489392.pdf	Pilote professionnel	2025-09-10 06:36:46	\N	\N	\N	\N
63	aip-fmep-1025-68c920f700d91852771683.pdf	AIP_FMEP_1025.pdf	2025-09-16 08:33:58	\N	\N	1	\N
\.


--
-- Data for Name: certificat_medical; Type: TABLE DATA; Schema: public; Owner: app
--

COPY public.certificat_medical (id, type, medecin, date_obtention, valid_until, validity_duration_months, remarques, created_at, updated_at, profil_id, is_alert_sent, created_by_id, updated_by_id, document_id) FROM stdin;
6	CNCI		2025-01-01 00:00:00	\N	0		2025-08-20 15:28:25	2026-01-29 15:16:16	11	f	\N	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4	\N
17	CNCI		2025-01-01 00:00:00	\N	0		\N	2026-01-29 15:16:16	27	f	\N	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4	\N
24	CNCI		2025-01-01 00:00:00	\N	0		2025-08-31 12:23:02	2026-01-29 15:16:16	34	f	\N	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4	4
11	CNCI		2025-01-01 00:00:00	\N	0		2025-08-20 15:28:25	2026-01-29 15:16:16	16	f	\N	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4	\N
32	CNCI		2025-01-01 00:00:00	\N	0		2025-09-10 16:28:24	2026-01-29 15:16:16	42	\N	1f08e632-c824-64e2-a414-ab9a9b6fd4ce	\N	\N
34	CNCI		2025-01-01 00:00:00	\N	0		2025-09-10 16:38:16	2026-01-29 15:16:16	44	\N	1f08e648-d1b0-60ae-8ffc-1328acbf9d90	\N	\N
35	CNCI		2025-01-01 00:00:00	\N	0		2025-09-10 16:44:30	2026-01-29 15:16:16	45	\N	1f08e656-c2c6-6c4c-b5be-c78fe5e67788	\N	\N
33	CNCI		2025-01-01 00:00:00	\N	0		2025-09-10 16:32:08	2026-01-29 15:16:16	43	f	1f08e63b-1f0a-6f7e-8c6c-19b4a2eaa76f	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4	61
28	CNCI		2025-01-01 00:00:00	\N	0		2025-09-10 05:56:47	2026-01-29 15:16:16	38	f	\N	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4	51
30	CNCI		2025-01-01 00:00:00	\N	0		2025-09-10 06:36:46	2026-01-29 15:16:16	40	f	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4	56
1	CNCI		2025-01-01 00:00:00	\N	0		\N	2026-01-29 15:16:16	8	f	\N	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4	42
16	CNCI		2025-01-01 00:00:00	\N	0		\N	2026-01-29 15:16:16	26	f	\N	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4	\N
31	CNCI		2025-01-01 00:00:00	\N	0		2025-09-10 14:26:33	2026-01-29 15:16:16	41	f	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4	\N
4	CNCI		2025-01-01 00:00:00	\N	0		\N	2026-01-29 15:16:16	10	\N	\N	\N	\N
7	CNCI		2025-01-01 00:00:00	\N	0		2025-08-20 15:28:25	2026-01-29 15:16:16	12	\N	\N	\N	\N
8	CNCI		2025-01-01 00:00:00	\N	0		2025-08-20 15:28:25	2026-01-29 15:16:16	13	\N	\N	\N	\N
9	CNCI		2025-01-01 00:00:00	\N	0		2025-08-20 15:28:25	2026-01-29 15:16:16	14	\N	\N	\N	\N
14	CNCI		2025-01-01 00:00:00	\N	0		2025-08-20 15:28:25	2026-01-29 15:16:16	19	\N	\N	\N	\N
5	CNCI		2025-01-01 00:00:00	\N	0		\N	2026-01-29 15:16:16	25	\N	\N	\N	\N
12	CNCI		2025-01-01 00:00:00	\N	0		2025-08-20 15:28:25	2026-01-29 15:16:16	17	\N	\N	\N	\N
15	CNCI		2025-01-01 00:00:00	\N	0		2025-08-20 15:28:25	2026-01-29 15:16:16	23	\N	\N	\N	\N
3	CNCI		2025-01-01 00:00:00	\N	0		\N	2026-01-29 15:16:16	9	\N	\N	\N	\N
10	CNCI		2025-01-01 00:00:00	\N	0		2025-08-20 15:28:25	2026-01-29 15:16:16	15	\N	\N	\N	\N
13	CNCI		2025-01-01 00:00:00	\N	0		2025-08-20 15:28:25	2026-01-29 15:16:16	18	\N	\N	\N	\N
\.


--
-- Data for Name: qualification; Type: TABLE DATA; Schema: public; Owner: app
--

-- COPY public.qualification (id, nom, encadrant, color, slug) FROM stdin;
-- 1	Pilote professionnel	f	#fb923c	pro
-- 2	Instructeur	t	#f87171	instructeur
-- 7	Emport Passager	f	#a78bfa	emport-passager
-- 6	Multiaxes	f	#e879f9	multiaxes
-- 3	Vol photo	f	#2dd4bf	vol-photo
-- 4	Largage parachutiste	f	#60a5fa	largage-para
-- 9	Secrétariat	f	#4ade80	secretariat
-- \.


--
-- Data for Name: circuit_qualification; Type: TABLE DATA; Schema: public; Owner: app
--

COPY public.circuit_qualification (circuit_id, qualification_id) FROM stdin;
1	1
2	1
3	1
4	1
8	3
9	4
10	1
11	2
12	1
1	2
2	2
3	2
4	2
6	2
6	1
6	7
6	6
10	2
12	2
7	1
7	2
7	6
7	7
13	1
13	2
\.


--
-- Data for Name: contact; Type: TABLE DATA; Schema: public; Owner: app
--

-- COPY public.contact (id, name) FROM stdin;
-- 1	Téléphone
-- 2	Email
-- 3	SMS
-- 4	WhatsApp
-- 5	Physique
-- \.


--
-- Data for Name: disponibilite; Type: TABLE DATA; Schema: public; Owner: app
--

COPY public.disponibilite (id, debut, fin, motif, pilote_id) FROM stdin;
2	2025-09-03 20:00:00	2025-09-04 19:59:59	\N	16
1	2025-09-02 20:00:00	2025-09-23 19:59:59	Vacances	9
3	2025-09-05 20:00:00	2025-09-06 19:59:59	Repos	8
\.


--
-- Data for Name: doctrine_migration_versions; Type: TABLE DATA; Schema: public; Owner: app
--

-- COPY public.doctrine_migration_versions (version, executed_at, execution_time) FROM stdin;
-- DoctrineMigrations\\Version20250304124102	2025-04-21 14:12:54	40
-- DoctrineMigrations\\Version20250309125955	2025-04-21 14:12:54	6
-- DoctrineMigrations\\Version20250312111647	2025-04-21 14:12:54	2
-- DoctrineMigrations\\Version20250317194345	2025-04-21 14:12:54	3
-- DoctrineMigrations\\Version20250318050036	2025-04-21 14:12:54	1
-- DoctrineMigrations\\Version20250320151408	2025-04-21 14:12:54	20
-- DoctrineMigrations\\Version20250406123144	2025-04-21 14:12:54	4
-- DoctrineMigrations\\Version20250409135658	2025-04-21 14:12:54	3
-- DoctrineMigrations\\Version20250409141413	2025-04-21 14:12:54	10
-- DoctrineMigrations\\Version20250411132929	2025-04-21 14:12:54	1
-- DoctrineMigrations\\Version20250411142140	2025-04-21 14:12:54	0
-- DoctrineMigrations\\Version20250412063715	2025-04-21 14:12:54	5
-- DoctrineMigrations\\Version20250412064911	2025-04-21 14:12:54	4
-- DoctrineMigrations\\Version20250412065117	2025-04-21 14:12:54	2
-- DoctrineMigrations\\Version20250412065343	2025-04-21 14:12:54	2
-- DoctrineMigrations\\Version20250413091442	2025-04-21 14:12:54	5
-- DoctrineMigrations\\Version20250413112220	2025-04-21 14:12:54	5
-- DoctrineMigrations\\Version20250413155649	2025-04-21 14:12:54	1
-- DoctrineMigrations\\Version20250414114425	2025-04-21 14:12:54	0
-- DoctrineMigrations\\Version20250417064447	2025-04-21 14:12:54	3
-- DoctrineMigrations\\Version20250423105155	2025-04-23 10:52:09	8
-- DoctrineMigrations\\Version20250423110740	2025-04-23 11:07:47	11
-- DoctrineMigrations\\Version20250423165150	2025-04-23 16:52:03	10
-- DoctrineMigrations\\Version20250424104933	2025-04-24 10:49:43	7
-- DoctrineMigrations\\Version20250428075531	2025-04-28 07:55:41	19
-- DoctrineMigrations\\Version20250428154932	2025-04-28 15:49:56	24
-- DoctrineMigrations\\Version20250428163927	2025-04-28 16:40:03	15
-- DoctrineMigrations\\Version20250428165859	2025-04-28 16:59:19	13
-- DoctrineMigrations\\Version20250428170046	2025-04-28 17:01:23	15
-- DoctrineMigrations\\Version20250429032924	2025-04-29 03:33:17	21
-- DoctrineMigrations\\Version20250429055539	2025-04-29 05:55:48	11
-- DoctrineMigrations\\Version20250429084844	2025-04-29 08:48:53	10
-- DoctrineMigrations\\Version20250429151639	2025-04-29 15:17:11	12
-- DoctrineMigrations\\Version20250510070550	2025-05-10 07:06:43	18
-- DoctrineMigrations\\Version20250510134921	2025-05-10 13:49:32	15
-- DoctrineMigrations\\Version20250515124745	2025-05-15 12:47:54	11
-- DoctrineMigrations\\Version20250517063058	2025-05-17 06:31:09	13
-- DoctrineMigrations\\Version20250517082925	2025-05-17 08:29:33	7
-- DoctrineMigrations\\Version20250517085502	2025-05-17 08:55:12	6
-- DoctrineMigrations\\Version20250525063643	2025-05-25 06:36:53	10
-- DoctrineMigrations\\Version20250525135051	2025-05-25 13:51:00	9
-- DoctrineMigrations\\Version20250525145642	2025-05-25 14:56:53	7
-- DoctrineMigrations\\Version20250525190457	2025-05-25 19:05:12	16
-- DoctrineMigrations\\Version20250525191652	2025-05-25 19:18:04	19
-- DoctrineMigrations\\Version20250525193829	2025-05-25 19:38:39	11
-- DoctrineMigrations\\Version20250527144046	2025-05-27 14:41:35	14
-- DoctrineMigrations\\Version20250527144638	2025-05-27 14:46:48	11
-- DoctrineMigrations\\Version20250603170633	2025-06-03 17:06:44	9
-- DoctrineMigrations\\Version20250604150834	2025-06-04 15:08:46	21
-- DoctrineMigrations\\Version20250604152658	2025-06-04 15:28:33	6
-- DoctrineMigrations\\Version20250628095211	2025-06-28 09:52:22	15
-- DoctrineMigrations\\Version20250729131035	2025-07-29 13:11:21	19
-- DoctrineMigrations\\Version20250730050320	2025-07-30 05:03:31	7
-- DoctrineMigrations\\Version20250730171541	2025-07-30 17:15:54	20
-- DoctrineMigrations\\Version20250804051342	2025-08-04 05:13:54	12
-- DoctrineMigrations\\Version20250805025043	2025-08-05 02:54:20	15
-- DoctrineMigrations\\Version20250805103518	2025-08-05 10:38:06	25
-- DoctrineMigrations\\Version20250813140321	2025-08-13 14:03:45	13
-- DoctrineMigrations\\Version20250818061926	2025-08-18 06:19:36	33
-- DoctrineMigrations\\Version20250819062953	2025-08-19 06:30:04	24
-- DoctrineMigrations\\Version20250819183645	2025-08-19 18:36:54	24
-- DoctrineMigrations\\Version20250820060054	2025-08-20 06:01:34	6
-- DoctrineMigrations\\Version20250820165632	2025-08-20 16:56:57	13
-- DoctrineMigrations\\Version20250821043941	2025-08-21 04:39:52	11
-- DoctrineMigrations\\Version20250821064644	2025-08-21 06:46:55	16
-- DoctrineMigrations\\Version20250822165721	2025-08-22 16:57:39	13
-- DoctrineMigrations\\Version20250824150812	2025-08-24 15:08:30	48
-- DoctrineMigrations\\Version20250825034804	2025-08-25 03:48:31	25
-- DoctrineMigrations\\Version20250825045238	2025-08-25 04:52:46	18
-- DoctrineMigrations\\Version20250825125612	2025-08-25 12:56:20	20
-- DoctrineMigrations\\Version20250826161933	2025-08-26 16:19:42	6
-- DoctrineMigrations\\Version20250831070459	2025-08-31 07:05:09	24
-- DoctrineMigrations\\Version20250901144446	2025-09-01 14:45:02	12
-- DoctrineMigrations\\Version20250902114021	2025-09-02 11:41:21	15
-- DoctrineMigrations\\Version20250903124724	2025-09-03 12:54:28	33
-- DoctrineMigrations\\Version20250904054108	2025-09-04 05:43:25	29
-- DoctrineMigrations\\Version20250904110957	2025-09-04 11:11:23	14
-- DoctrineMigrations\\Version20250904124738	2025-09-04 12:48:36	14
-- DoctrineMigrations\\Version20250905073048	2025-09-05 07:31:36	16
-- DoctrineMigrations\\Version20250905130924	2025-09-05 13:09:55	13
-- DoctrineMigrations\\Version20250906155507	2025-09-06 15:55:42	29
-- DoctrineMigrations\\Version20250907132715	2025-09-07 13:28:21	16
-- DoctrineMigrations\\Version20250907162020	2025-09-07 16:20:56	10
-- DoctrineMigrations\\Version20250913180506	2025-09-13 18:05:42	13
-- DoctrineMigrations\\Version20250914142134	2025-09-14 14:22:19	20
-- DoctrineMigrations\\Version20250914150432	2025-09-14 15:05:10	7
-- DoctrineMigrations\\Version20250918174425	2025-09-18 17:45:02	13
-- \.


--
-- Data for Name: entretien_user; Type: TABLE DATA; Schema: public; Owner: app
--

COPY public.entretien_user (entretien_id, user_id) FROM stdin;
3	1f000a01-0394-65de-b10d-c39afce9e08d
3	1effe8dc-f73d-600a-bd2c-3324ddd39a2e
4	1effe8dc-f73d-600a-bd2c-3324ddd39a2e
4	1f000a01-0394-65de-b10d-c39afce9e08d
5	1f000a01-0394-65de-b10d-c39afce9e08d
5	1effe8dc-f73d-600a-bd2c-3324ddd39a2e
6	1f000a01-0394-65de-b10d-c39afce9e08d
6	1f000a06-9819-686c-9b8d-c39afce9e08d
7	1effe8dc-f73d-600a-bd2c-3324ddd39a2e
7	1f000a01-0394-65de-b10d-c39afce9e08d
\.


--
-- Data for Name: expense; Type: TABLE DATA; Schema: public; Owner: app
--

COPY public.expense (id, date, beneficiaire, libelle, related_to_maintenance, document_id, entretien_id, tva, total_ht, total_ttc) FROM stdin;
1	2025-09-07 16:17:19	ACCU-RUN	Achat Batterie TX	t	48	3	0.085	429.49	466
2	2025-09-08 12:21:11	Sébastien MAILLOT	Salaire Août 2025	f	47	\N	0	1944	1944
\.


--
-- Data for Name: prestation; Type: TABLE DATA; Schema: public; Owner: app
--

COPY public.prestation (id, date, duree, horametre_depart, horametre_fin, turnover, aeronef_id, pilote_id, remarques, encadrant_id, created_at, updated_at, created_by_id, updated_by_id) FROM stdin;
1	2025-03-13 05:12:13	1	4132.6	4133.6	170	1	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	Rien à signaler.	\N	\N	\N	\N	\N
4	2025-03-14 04:10:14	1	4133.6	4134.6	170	1	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	Rien à signaler.	\N	\N	\N	\N	\N
6	2025-03-14 07:08:09	1	4134.6	4135.6	0	1	1f000a06-9819-686c-9b8d-c39afce9e08d	Rien à signaler.	\N	\N	\N	\N	\N
9	2025-03-16 07:01:11	2.56	2169.48	2172.44	500	3	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	Rien à signaler.	\N	\N	\N	\N	\N
10	2025-03-20 15:41:16	2.18	2172.44	2175.02	380	3	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	Rien à signaler.	\N	\N	\N	\N	\N
11	2025-03-21 06:06:47	1.03	2175.02	2176.05	170	3	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	Rien à signaler.	\N	\N	\N	\N	\N
8	2025-03-16 06:59:59	2.9	4135.6	4138.5	500	1	1f000a01-0394-65de-b10d-c39afce9e08d	Rien à signaler.	\N	\N	\N	\N	\N
12	2025-03-19 20:00:00	1.03	217.28	218.31	170	4	1f000a01-0394-65de-b10d-c39afce9e08d	Rien à signaler.	\N	\N	\N	\N	\N
13	2025-03-19 20:00:00	2.3	4138.5	4140.8	380	1	1f000a03-4784-69e0-85cf-c39afce9e08d	Rien à signaler.	\N	\N	\N	\N	\N
14	2025-03-19 20:00:00	2.07	4184.21	4186.28	340	2	1f000a05-b11b-699c-8cc6-c39afce9e08d	Rien à signaler.	\N	\N	\N	\N	\N
15	2025-03-20 20:00:00	1.05	4186.28	4187.33	170	2	1f000a05-b11b-699c-8cc6-c39afce9e08d	Rien à signaler.	\N	\N	\N	\N	\N
16	2025-03-21 20:00:00	1.16	4187.33	4188.49	171.45	2	1f000a03-4784-69e0-85cf-c39afce9e08d	Rien à signaler.	\N	\N	\N	\N	\N
17	2025-03-21 20:00:00	0.44	2176.05	2176.49	98.55	3	1f000a01-0394-65de-b10d-c39afce9e08d	Rien à signaler.	\N	\N	\N	\N	\N
18	2025-03-22 20:00:00	0.46	4188.49	4189.35	84.7	2	1f008640-2de8-6392-8a46-1f3c824a438d	Rien à signaler.	\N	\N	\N	\N	\N
19	2025-03-24 06:23:39	0.56	218.31	219.27	170	4	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	Rien à signaler.	\N	\N	\N	\N	\N
20	2025-03-24 06:24:24	1.14	4189.35	4190.49	210	2	1f000a03-4784-69e0-85cf-c39afce9e08d	Rien à signaler.	\N	\N	\N	\N	\N
55	2025-04-05 05:57:58	2.28	4212.25	4214.53	420	2	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	Rien à signaler.	\N	\N	\N	\N	\N
21	2025-03-24 06:24:24	1.05	4190.49	4191.54	-1382.4	2	1f000a03-4784-69e0-85cf-c39afce9e08d	Rien à signaler.	\N	\N	\N	\N	\N
22	2025-03-24 06:31:24	2.05	2176.49	2178.54	380	3	1f000a05-b11b-699c-8cc6-c39afce9e08d	Rien à signaler.	\N	\N	\N	\N	\N
56	2025-04-07 05:46:47	2.16	4214.53	4217.09	400	2	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	Rien à signaler.	\N	\N	\N	\N	\N
23	2025-03-25 05:22:08	0.58	4191.54	4192.52	170	2	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	Rien à signaler.	\N	\N	\N	\N	\N
26	2025-03-26 04:19:58	1.16	4192.52	4194.08	210	2	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	Rien à signaler.	\N	\N	\N	\N	\N
27	2025-03-26 05:26:48	2.19	2178.54	2181.13	380	3	1f000a03-4784-69e0-85cf-c39afce9e08d	Rien à signaler.	\N	\N	\N	\N	\N
28	2025-03-26 05:26:48	2.17	220.31	222.48	380	4	1f00971f-f3f5-6d56-b729-1315ef24fbc8	Rien à signaler.	\N	\N	\N	\N	\N
29	2025-03-28 06:39:13	3.09	4194.08	4197.17	520	2	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	Rien à signaler.	\N	\N	\N	\N	\N
30	2025-03-28 06:40:39	3.09	222.48	225.57	520	4	1f000a05-b11b-699c-8cc6-c39afce9e08d	Rien à signaler.	\N	\N	\N	\N	\N
31	2025-03-29 05:31:28	1.08	225.57	227.05	0	4	1f000a03-4784-69e0-85cf-c39afce9e08d	Rien à signaler.	\N	\N	\N	\N	\N
33	2025-03-29 05:32:17	1.08	4197.17	4198.25	0	2	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	Rien à signaler.	\N	\N	\N	\N	\N
32	2025-03-29 05:31:28	0.42	227.05	227.47	0	4	1f00c5ed-cdd0-69d0-8372-8f7aea458831	Rien à signaler.	\N	\N	\N	\N	\N
34	2025-03-29 05:32:17	1.05	4198.25	4199.3	170	2	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	Rien à signaler.	\N	\N	\N	\N	\N
35	2025-03-29 05:38:10	1.9	4140.8	4142.7	310	1	1f000a05-b11b-699c-8cc6-c39afce9e08d	Rien à signaler.	\N	\N	\N	\N	\N
36	2025-03-30 04:20:50	1.17	4199.3	4200.47	210	2	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	Rien à signaler.	\N	\N	\N	\N	\N
37	2025-03-30 04:40:04	1.2	4142.7	4143.9	210	1	1f000a05-b11b-699c-8cc6-c39afce9e08d	Rien à signaler.	\N	\N	\N	\N	\N
38	2025-03-27 20:00:00	1.05	2181.13	2182.18	170	3	1f000a03-4784-69e0-85cf-c39afce9e08d	Rien à signaler.	\N	\N	\N	\N	\N
39	2025-03-27 20:00:00	1.05	2181.13	2182.18	170	3	1f000a03-4784-69e0-85cf-c39afce9e08d	Rien à signaler.	\N	\N	\N	\N	\N
40	2025-03-29 20:00:00	1.15	227.47	229.02	210	4	1f000a03-4784-69e0-85cf-c39afce9e08d	Rien à signaler.	\N	\N	\N	\N	\N
41	2025-03-31 05:53:28	2.19	4200.47	4203.06	435	2	1f000a03-4784-69e0-85cf-c39afce9e08d	Rien à signaler.	\N	\N	\N	\N	\N
42	2025-04-01 04:20:49	1.19	4203.06	4204.25	210	2	1f000a05-b11b-699c-8cc6-c39afce9e08d	Rien à signaler.	\N	\N	\N	\N	\N
57	2025-04-09 06:11:49	1.15	4217.09	4218.24	210	2	1f000a01-0394-65de-b10d-c39afce9e08d	Rien à signaler.	\N	\N	\N	\N	\N
58	2025-04-09 06:15:22	1.03	4218.24	4219.27	0	2	1f000a01-0394-65de-b10d-c39afce9e08d	Atelier dAmel	\N	\N	\N	\N	\N
46	2025-03-31 20:00:00	0.56	4204.25	4205.21	102.3	2	1f008640-2de8-6392-8a46-1f3c824a438d	Rien à signaler.	\N	\N	\N	\N	\N
47	2025-03-31 20:00:00	1.02	4205.21	4206.23	170	2	1f000a01-0394-65de-b10d-c39afce9e08d	Rien à signaler.	\N	\N	\N	\N	\N
48	2025-04-02 08:01:05	0.12	4206.23	4206.35	0	2	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	Rien à signaler.	\N	\N	\N	\N	\N
49	2025-04-03 05:54:48	2.27	4206.35	4209.02	420	2	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	Rien à signaler.	\N	\N	\N	\N	\N
50	2025-04-04 06:58:08	3.23	4209.02	4212.25	550	2	1f000a03-4784-69e0-85cf-c39afce9e08d	Rien à signaler.	\N	\N	\N	\N	\N
51	2025-04-04 06:58:08	3.4	4148.3	4151.7	550	1	1f00971f-f3f5-6d56-b729-1315ef24fbc8	Rien à signaler.	\N	\N	\N	\N	\N
52	2025-04-05 05:49:12	1.2	4151.7	4152.9	210	1	1f000a03-4784-69e0-85cf-c39afce9e08d	Rien à signaler.	\N	\N	\N	\N	\N
53	2025-04-05 05:50:09	1.1	4152.9	4154	148.5	1	1f00c5ed-cdd0-69d0-8372-8f7aea458831	Rien à signaler.	\N	\N	\N	\N	\N
54	2025-04-05 05:55:04	0.43	229.02	229.45	97.2	4	1f011e26-6b9f-604a-a24e-6900d92931ed	Rien à signaler.	\N	\N	\N	\N	\N
59	2025-04-09 07:15:15	3.37	2182.18	2185.55	590	3	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	Rien à signaler.	\N	\N	\N	\N	\N
67	2025-04-11 08:12:47	1	4219.27	4220.27	170	2	1f000a01-0394-65de-b10d-c39afce9e08d	Rien à signaler.	\N	\N	\N	\N	\N
60	2025-04-07 00:00:00	2.15	229.45	232	590	4	1f000a03-4784-69e0-85cf-c39afce9e08d	Rien à signaler.	\N	\N	\N	\N	\N
61	2025-04-09 07:25:37	3.38	232	235.38	630	4	1f000a03-4784-69e0-85cf-c39afce9e08d	Rien à signaler.	\N	\N	\N	\N	\N
62	2025-04-10 04:27:28	1.15	2185.55	2187.1	137.5	3	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	Rien à signaler.	\N	\N	\N	\N	\N
63	2025-04-11 07:28:43	3.44	235.38	239.22	630	4	1f000a03-4784-69e0-85cf-c39afce9e08d	Rien à signaler.	\N	\N	\N	\N	\N
64	2025-04-11 07:30:05	3.5	4154	4157.5	590	1	1f000a05-b11b-699c-8cc6-c39afce9e08d	Rien à signaler.	\N	\N	\N	\N	\N
65	2025-04-11 07:29:44	2.56	2187.1	2190.06	590	3	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	Rien à signaler.	\N	\N	\N	\N	\N
69	2025-04-12 06:17:11	0.51	2190.06	2190.57	114.75	3	1f011e26-6b9f-604a-a24e-6900d92931ed	SOLO TDP	\N	\N	\N	\N	\N
70	2025-04-12 07:35:29	0.54	2190.57	2191.51	0	3	1f000a07-a2bc-6da4-bcac-c39afce9e08d	CAMBAIE	\N	\N	\N	\N	\N
71	2025-04-14 06:41:04	3.25	2191.51	2195.16	560	3	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	Rien à signaler.	\N	\N	\N	\N	\N
72	2025-04-14 11:55:01	3.5	4157.5	4161	630	1	1f000a05-b11b-699c-8cc6-c39afce9e08d	Rien à signaler.	\N	\N	\N	\N	\N
73	2025-04-15 06:23:51	3	4161	4164	510	1	1f000a03-4784-69e0-85cf-c39afce9e08d	Rien à signaler.	\N	\N	\N	\N	\N
74	2025-04-13 20:00:00	3.15	239.22	242.37	520	4	1f000a03-4784-69e0-85cf-c39afce9e08d	Rien à signaler.	\N	\N	\N	\N	\N
76	2025-04-15 08:29:44	0	245.45	245.45	510	4	1f000a05-b11b-699c-8cc6-c39afce9e08d	Rien à signaler.	\N	\N	\N	\N	\N
77	2025-04-16 06:45:22	3.06	245.45	248.51	510	4	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	Rien à signaler.	\N	\N	\N	\N	\N
78	2025-04-16 07:39:10	3.1	4164	4167.1	510	1	1f000a03-4784-69e0-85cf-c39afce9e08d	Rien à signaler.	\N	\N	\N	\N	\N
79	2025-04-18 06:34:31	2.1	4220.27	4222.37	340	2	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	Rien à signaler.	\N	\N	\N	\N	\N
80	2025-04-18 06:40:47	1.43	2195.16	2196.59	270	3	1f000a03-4784-69e0-85cf-c39afce9e08d	Rien à signaler.	\N	\N	\N	\N	\N
81	2025-04-18 06:43:33	3	4167.1	4170.1	480	1	1f000a05-b11b-699c-8cc6-c39afce9e08d	Rien à signaler.	\N	\N	\N	\N	\N
82	2025-04-19 06:53:04	3.1	4170.1	4173.2	510	1	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	Rien à signaler.	\N	\N	\N	\N	\N
83	2025-04-19 06:55:52	3.11	4222.37	4225.48	510	2	1f000a05-b11b-699c-8cc6-c39afce9e08d	Rien à signaler.	\N	\N	\N	\N	\N
84	2025-04-20 07:15:56	1.1	4173.2	4174.3	170	1	1f000a01-0394-65de-b10d-c39afce9e08d	Rien à signaler.	\N	\N	\N	\N	\N
85	2025-04-20 07:16:36	1.16	4225.48	4227.04	139.7	2	1f000a07-a2bc-6da4-bcac-c39afce9e08d	Rien à signaler.	\N	\N	\N	\N	\N
86	2025-04-20 07:53:21	0.7	4174.3	4175	94.5	1	1f01db88-7afb-6cda-a7dd-515d112c8245	Rien à signaler.	\N	\N	\N	\N	\N
45	2025-04-01 08:59:21	3.9	4143.9	4147.8	760	1	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	Rien à signaler.	\N	\N	\N	\N	\N
88	2025-04-01 08:00:00	0.5	4147.8	4148.3	0	1	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	Rien à signaler.	\N	\N	\N	\N	\N
87	2025-04-22 08:00:00	2.1	4175	4177.1	340	1	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	Rien à signaler.	\N	\N	\N	\N	\N
90	2025-04-29 08:00:00	2.4	4177.1	4179.5	380	1	1f024e74-ad22-6808-a0cc-9b7b2ff302fc	Rien à signaler.	\N	\N	\N	\N	\N
94	2025-05-28 08:00:00	1.3	4180.6	4181.9	210	1	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	Rien à signaler.	\N	\N	\N	\N	\N
107	2025-08-19 08:00:00	0.54	4233.27	4234.21	99	2	1f011e26-6b9f-604a-a24e-6900d92931ed	Rien à signaler.	\N	2025-08-19 11:44:30	2025-08-19 11:58:56	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4
106	2025-08-13 08:00:00	4.03	4229.24	4233.27	680	2	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	Rien à signaler.	\N	\N	2025-08-19 11:59:13	\N	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4
91	2025-04-29 08:00:00	1.1	4179.5	4180.6	148.5	1	1f01db88-7afb-6cda-a7dd-515d112c8245	Rien à signaler.	1f000a01-0394-65de-b10d-c39afce9e08d	\N	\N	\N	\N
92	2025-05-28 08:00:00	1.3	4180.6	4181.9	210	1	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	Rien à signaler.	\N	\N	\N	\N	\N
108	2025-08-19 08:00:00	0.7	4188	4188.7	94.5	1	1f01db88-7afb-6cda-a7dd-515d112c8245	Rien à signaler.	1f000a03-4784-69e0-85cf-c39afce9e08d	2025-08-19 14:11:14	2025-08-19 17:54:53	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4
95	2025-05-28 08:00:00	1.17	248.51	250.08	210	4	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	Rien à signaler.	\N	\N	\N	\N	\N
96	2025-05-28 08:00:00	1.4	4180.6	4182	0	1	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	Rien à signaler.	1f000a01-0394-65de-b10d-c39afce9e08d	\N	\N	\N	\N
93	2025-05-28 08:00:00	1.5	4227.04	4228.55	310	2	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	Rien à signaler.	\N	\N	\N	\N	\N
97	2025-06-03 08:00:00	0.3	4180.6	4180.9	0	1	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	Rien à signaler.	\N	\N	\N	\N	\N
98	2025-06-03 08:00:00	2.15	2196.59	2199.14	380	3	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	Rien à signaler.	\N	\N	\N	\N	\N
99	2025-06-08 08:00:00	1.4	4180.6	4182	210	1	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	Rien à signaler.	\N	\N	\N	\N	\N
100	2025-06-08 08:00:00	1.4	4182	4183.4	210	1	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	Rien à signaler.	\N	\N	\N	\N	\N
102	2025-06-08 08:00:00	1.1	4183.4	4184.5	210	1	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	Rien à signaler.	\N	\N	\N	\N	\N
103	2025-06-08 08:00:00	1.18	4227.04	4228.22	210	2	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	Rien à signaler.	\N	\N	\N	\N	\N
104	2025-06-08 08:00:00	1.02	4228.22	4229.24	170	2	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	Rien à signaler.	\N	\N	\N	\N	\N
105	2025-08-11 08:00:00	3.5	4184.5	4188	590	1	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	Rien à signaler.	\N	\N	\N	\N	\N
109	2025-08-20 08:00:00	1.2	4188.7	4189.9	210	1	1f000a03-4784-69e0-85cf-c39afce9e08d	Rien à signaler.	\N	2025-08-20 10:11:15	\N	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4	\N
111	2025-08-20 08:00:00	1.15	4237.54	4239.09	210	2	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	Rien à signaler.	1f000a01-0394-65de-b10d-c39afce9e08d	2025-08-20 16:14:16	2025-08-20 16:15:34	1f07dcbe-c3eb-6520-b05d-9b2e91069048	1f07dcbe-c3eb-6520-b05d-9b2e91069048
112	2025-08-22 08:00:00	2	4239.09	4241.09	340	2	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	Rien à signaler.	\N	2025-08-22 13:32:19	\N	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4	\N
113	2025-08-23 08:00:00	3.7	4189.9	4193.6	630	1	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	Rien à signaler.	\N	2025-08-22 13:35:06	\N	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4	\N
114	2025-08-22 08:00:00	3.3	248.51	252.21	590	4	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	Rien à signaler.	\N	2025-08-22 13:38:19	\N	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4	\N
115	2025-08-22 08:00:00	3.4	4241.09	4244.49	620	2	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	Rien à signaler.	\N	2025-08-22 13:43:12	\N	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4	\N
116	2025-08-22 08:00:00	5	4193.6	4198.6	840	1	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4	Rien à signaler.	\N	2025-08-22 13:47:55	\N	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4	\N
117	2025-08-22 08:00:00	5	4244.49	4249.49	840	2	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4	Rien à signaler.	\N	2025-08-22 13:52:41	\N	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4	\N
118	2025-08-22 08:00:00	4.05	252.21	256.26	690	4	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4	Rien à signaler.	\N	2025-08-22 13:55:53	\N	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4	\N
110	2025-08-18 08:00:00	3.33	4234.21	4237.54	590	2	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	Rien à signaler.	\N	2025-08-20 10:20:06	2025-08-20 10:54:41	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4
119	2025-08-22 08:00:00	2.32	2196.59	2199.31	420	3	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4	Rien à signaler.	\N	2025-08-22 13:57:03	\N	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4	\N
120	2025-08-22 08:00:00	4.25	4249.49	4254.14	730	2	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4	Rien à signaler.	\N	2025-08-22 14:03:14	\N	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4	\N
121	2025-08-23 08:00:00	4.12	256.26	260.38	680	4	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	Rien à signaler.	\N	2025-08-23 14:01:06	\N	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4	\N
122	2025-08-23 08:00:00	2.19	260.38	262.57	380	4	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	Rien à signaler.	\N	2025-08-23 14:02:07	\N	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4	\N
123	2025-08-23 08:00:00	4.03	4254.14	4258.17	680	2	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4	Rien à signaler.	\N	2025-08-23 14:13:14	\N	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4	\N
124	2025-08-23 08:00:00	3.51	4258.17	4262.08	630	2	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4	Rien à signaler.	\N	2025-08-23 14:14:20	\N	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4	\N
125	2025-08-23 08:00:00	4.12	4262.08	4266.2	680	2	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4	Rien à signaler.	\N	2025-08-23 14:18:57	\N	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4	\N
126	2025-08-23 08:00:00	1.16	262.57	264.13	210	4	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4	Rien à signaler.	\N	2025-08-23 14:58:52	\N	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4	\N
127	2025-08-23 08:00:00	1.3	4198.6	4199.9	210	1	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	Rien à signaler.	\N	2025-08-23 14:59:15	\N	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4	\N
128	2025-08-24 08:00:00	1.3	4199.9	4201.2	210	1	1f024e74-ad22-6808-a0cc-9b7b2ff302fc	Rien à signaler.	\N	2025-08-24 05:51:19	\N	1f024e74-ad22-6808-a0cc-9b7b2ff302fc	\N
129	2025-08-24 08:00:00	2.18	4266.2	4268.38	380	2	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	Rien à signaler.	\N	2025-08-24 13:56:10	\N	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4	\N
130	2025-08-24 08:00:00	0.8	4201.2	4202	140	1	1f000a01-0394-65de-b10d-c39afce9e08d	Rien à signaler.	\N	2025-08-24 13:57:24	\N	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4	\N
131	2025-08-24 08:00:00	2.18	4268.38	4270.56	380	2	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	Rien à signaler.	\N	2025-08-24 13:59:44	\N	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4	\N
132	2025-08-24 08:00:00	0.8	4202	4202.8	140	1	1f000a01-0394-65de-b10d-c39afce9e08d	Rien à signaler.	\N	2025-08-24 14:00:50	\N	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4	\N
133	2025-08-25 08:00:00	1.53	4270.56	4272.49	310	2	1f000a01-0394-65de-b10d-c39afce9e08d	Rien à signaler.	\N	2025-08-25 15:22:32	\N	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4	\N
134	2025-08-30 08:00:00	4.38	2199.31	2204.09	760	3	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	Rien à signaler.	\N	2025-08-30 17:12:05	\N	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4	\N
135	2025-08-30 08:00:00	4.39	4272.49	4277.28	760	2	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4	Rien à signaler.	\N	2025-08-30 17:13:04	\N	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4	\N
136	2025-08-30 08:00:00	4.3	4202.8	4207.1	690	1	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4	Rien à signaler.	\N	2025-08-30 17:16:38	\N	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4	\N
139	2025-09-04 08:00:00	3.19	4188.7	4192.29	550	2	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	Rien à signaler.	\N	2025-09-04 12:10:20	\N	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4	\N
138	2025-09-04 08:00:00	1.2	4181.9	4183.1	132	1	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	Rien à signaler.	\N	2025-09-04 11:12:04	2025-09-04 11:28:50	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4
140	2025-09-04 08:00:00	3.19	264.13	267.32	550	4	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	Rien à signaler.	\N	2025-09-04 17:58:54	\N	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4	\N
141	2025-09-05 08:00:00	2.13	4192.29	4194.42	380	2	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	Rien à signaler.	\N	2025-09-05 15:38:33	\N	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4	\N
142	2025-09-05 08:00:00	3.23	4194.42	4198.05	560	2	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	Rien à signaler.	\N	2025-09-05 15:43:11	\N	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4	\N
143	2025-09-05 08:00:00	2.13	4198.05	4200.18	380	2	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4	Rien à signaler.	\N	2025-09-05 15:44:33	\N	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4	\N
144	2025-09-11 08:00:00	3.02	4200.18	4203.2	510	2	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	Rien à signaler.	\N	2025-09-11 13:07:45	\N	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4	\N
145	2025-09-12 08:00:00	2.19	4203.2	4205.39	380	2	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4	Rien à signaler.	\N	2025-09-12 07:48:52	\N	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4	\N
146	2025-09-14 08:00:00	3.15	4205.39	4208.54	550	2	1f08e51e-d4bb-6e5e-ac48-c9cbe02f0cce	Rien à signaler.	\N	2025-09-14 13:15:15	\N	1f08e51e-d4bb-6e5e-ac48-c9cbe02f0cce	\N
\.


--
-- Data for Name: vol; Type: TABLE DATA; Schema: public; Owner: app
--

COPY public.vol (id, quantite, duree, prix, circuit_id, prestation_id, option_id, cout, created_at, updated_at, created_by_id, updated_by_id) FROM stdin;
1	1	1	170	2	1	\N	\N	\N	\N	\N	\N
4	1	1	170	2	4	\N	\N	\N	\N	\N	\N
6	1	1	0	7	6	\N	\N	\N	\N	\N	\N
8	2	1	340	2	8	\N	\N	\N	\N	\N	\N
9	1	0.83333333333333	160	3	8	1	\N	\N	\N	\N	\N
10	2	2	340	2	9	\N	\N	\N	\N	\N	\N
11	1	0.5	160	3	9	1	\N	\N	\N	\N	\N
12	1	1.15	210	1	10	\N	\N	\N	\N	\N	\N
13	1	1	170	2	10	\N	\N	\N	\N	\N	\N
14	1	1	170	2	11	\N	\N	\N	\N	\N	\N
15	1	1	170	2	12	\N	\N	\N	\N	\N	\N
16	1	1.25	210	1	13	\N	\N	\N	\N	\N	\N
17	1	1	170	2	13	\N	\N	\N	\N	\N	\N
18	2	2	340	2	14	\N	\N	\N	\N	\N	\N
19	1	1	170	2	15	\N	\N	\N	\N	\N	\N
20	1	1.16	171.45	5	16	\N	\N	\N	\N	\N	\N
21	1	0.44	98.55	5	17	\N	\N	\N	\N	\N	\N
22	1	0.46	84.7	6	18	\N	\N	\N	\N	\N	\N
23	1	1	170	2	19	\N	\N	\N	\N	\N	\N
24	1	1.15	210	1	20	\N	\N	\N	\N	\N	\N
26	1	1.15	210	1	22	\N	\N	\N	\N	\N	\N
27	1	1	170	2	22	2	\N	\N	\N	\N	\N
28	1	1	170	2	23	\N	\N	\N	\N	\N	\N
25	1	1.05	185	8	21	\N	\N	\N	\N	\N	\N
31	1	1.15	210	1	26	\N	\N	\N	\N	\N	\N
32	1	1	170	2	27	\N	\N	\N	\N	\N	\N
33	1	1.15	210	1	27	\N	\N	\N	\N	\N	\N
34	1	1	170	2	28	\N	\N	\N	\N	\N	\N
35	1	1.15	210	1	28	\N	\N	\N	\N	\N	\N
36	1	1.15	210	1	29	\N	\N	\N	\N	\N	\N
37	1	1	170	2	29	\N	\N	\N	\N	\N	\N
38	1	0.5	140	3	29	\N	\N	\N	\N	\N	\N
39	1	1.15	210	1	30	\N	\N	\N	\N	\N	\N
40	1	1	170	2	30	\N	\N	\N	\N	\N	\N
41	1	0.5	140	3	30	\N	\N	\N	\N	\N	\N
42	1	1.08	0	7	31	\N	\N	\N	\N	\N	\N
43	1	1.5	0	7	32	\N	\N	\N	\N	\N	\N
44	1	1.08	0	7	33	\N	\N	\N	\N	\N	\N
45	1	1	170	2	34	\N	\N	\N	\N	\N	\N
46	1	1	170	2	35	\N	\N	\N	\N	\N	\N
47	1	0.83333333333333	140	3	35	\N	\N	\N	\N	\N	\N
48	1	1.15	210	1	36	\N	\N	\N	\N	\N	\N
49	1	1.25	210	1	37	\N	\N	\N	\N	\N	\N
50	1	1	170	2	38	\N	\N	\N	\N	\N	\N
51	1	1	170	2	39	\N	\N	\N	\N	\N	\N
52	1	1.15	210	1	40	\N	\N	\N	\N	\N	\N
53	1	0.45	130	4	41	\N	\N	\N	\N	\N	\N
54	1	0.3	135	11	41	\N	\N	\N	\N	\N	\N
55	1	1	170	2	41	\N	\N	\N	\N	\N	\N
56	1	1.15	210	1	42	\N	\N	\N	\N	\N	\N
59	3	1.25	630	1	45	\N	\N	\N	\N	\N	\N
61	1	0.56	102.3	6	46	\N	\N	\N	\N	\N	\N
62	1	1	170	2	47	\N	\N	\N	\N	\N	\N
63	1	0.12	0	10	48	\N	\N	\N	\N	\N	\N
64	2	2.3	420	1	49	\N	\N	\N	\N	\N	\N
65	1	1.15	210	1	50	\N	\N	\N	\N	\N	\N
66	2	2	340	2	50	\N	\N	\N	\N	\N	\N
67	1	1.25	210	1	51	\N	\N	\N	\N	\N	\N
68	2	1	340	2	51	\N	\N	\N	\N	\N	\N
69	1	1.25	210	1	52	\N	\N	\N	\N	\N	\N
70	1	1.1	148.5	5	53	\N	\N	\N	\N	\N	\N
71	1	0.43	97.2	5	54	\N	\N	\N	\N	\N	\N
72	2	2.3	420	1	55	\N	\N	\N	\N	\N	\N
73	1	1.15	230	1	56	1	\N	\N	\N	\N	\N
74	1	1	170	2	56	2	\N	\N	\N	\N	\N
75	1	1.15	210	1	57	\N	\N	\N	\N	\N	\N
76	1	1.03	0	7	58	\N	\N	\N	\N	\N	\N
77	1	1.15	210	1	59	\N	\N	\N	\N	\N	\N
78	1	1	170	2	59	\N	\N	\N	\N	\N	\N
79	1	1.15	210	1	59	2	\N	\N	\N	\N	\N
80	1	1.15	210	1	60	\N	\N	\N	\N	\N	\N
81	1	1	170	2	60	\N	\N	\N	\N	\N	\N
83	2	2.3	420	1	61	\N	\N	\N	\N	\N	\N
84	1	1.15	210	1	61	\N	\N	\N	\N	\N	\N
85	1	1.15	137.5	6	62	\N	\N	\N	\N	\N	\N
86	3	3.45	630	1	63	\N	\N	\N	\N	\N	\N
87	2	1.25	420	1	64	\N	\N	\N	\N	\N	\N
88	1	1	170	2	64	\N	\N	\N	\N	\N	\N
89	2	2.3	420	1	65	\N	\N	\N	\N	\N	\N
90	1	1	170	2	65	\N	\N	\N	\N	\N	\N
92	1	1	170	2	67	\N	\N	\N	\N	\N	\N
94	1	0.51	114.75	5	69	\N	\N	\N	\N	\N	\N
95	1	0.54	0	7	70	\N	\N	\N	\N	\N	\N
96	2	2.3	420	1	71	\N	\N	\N	\N	\N	\N
97	1	0.5	140	3	71	\N	\N	\N	\N	\N	\N
98	2	1.25	460	1	72	1	\N	\N	\N	\N	\N
99	1	1	170	2	72	\N	\N	\N	\N	\N	\N
100	3	1	510	2	73	\N	\N	\N	\N	\N	\N
101	1	1	170	2	74	\N	\N	\N	\N	\N	\N
102	1	0.5	140	3	74	\N	\N	\N	\N	\N	\N
103	1	1.15	210	1	74	\N	\N	\N	\N	\N	\N
105	3	3	510	2	76	\N	\N	\N	\N	\N	\N
106	3	3	510	2	77	\N	\N	\N	\N	\N	\N
107	3	1	510	2	78	\N	\N	\N	\N	\N	\N
108	1	1.15	210	1	79	\N	\N	\N	\N	\N	\N
109	1	0.45	130	4	79	\N	\N	\N	\N	\N	\N
110	1	0.5	140	3	80	\N	\N	\N	\N	\N	\N
111	1	0.45	130	4	80	\N	\N	\N	\N	\N	\N
112	1	1.25	210	1	81	\N	\N	\N	\N	\N	\N
113	1	0.83333333333333	140	3	81	\N	\N	\N	\N	\N	\N
114	1	0.75	130	4	81	\N	\N	\N	\N	\N	\N
115	1	1.25	210	1	82	\N	\N	\N	\N	\N	\N
116	1	1	170	2	82	\N	\N	\N	\N	\N	\N
117	1	0.75	130	4	82	\N	\N	\N	\N	\N	\N
118	1	1.15	210	1	83	\N	\N	\N	\N	\N	\N
119	1	1	170	2	83	\N	\N	\N	\N	\N	\N
120	1	0.45	130	4	83	\N	\N	\N	\N	\N	\N
121	1	1	170	2	84	\N	\N	\N	\N	\N	\N
122	1	1.16	139.7	6	85	\N	\N	\N	\N	\N	\N
123	1	0.7	94.5	5	86	\N	\N	\N	\N	\N	\N
124	2	1	340	2	87	\N	\N	\N	\N	\N	\N
133	1	1.25	210	1	94	\N	\N	\N	\N	\N	\N
125	1	0.5	90	12	88	\N	\N	\N	\N	\N	\N
127	1	1.25	210	1	90	\N	\N	\N	\N	\N	\N
128	1	1	170	2	90	\N	\N	\N	\N	\N	\N
129	1	1.1	148.5	5	91	\N	\N	\N	\N	\N	\N
130	1	1.25	210	1	92	\N	\N	\N	\N	\N	\N
131	1	1	170	2	93	\N	\N	\N	\N	\N	\N
132	1	0.5	140	3	93	\N	\N	\N	\N	\N	\N
134	1	1.15	210	1	95	\N	\N	\N	\N	\N	\N
135	1	1.4	0	7	96	\N	\N	\N	\N	\N	\N
136	1	0.3	0	10	97	\N	\N	\N	\N	\N	\N
137	1	1	170	2	98	\N	\N	\N	\N	\N	\N
138	1	1.15	210	1	98	\N	\N	\N	\N	\N	\N
139	1	1.25	210	1	99	\N	\N	\N	\N	\N	\N
140	1	1.25	210	1	100	\N	\N	\N	\N	\N	\N
142	1	1.25	210	1	102	\N	\N	\N	\N	\N	\N
143	1	1.15	210	1	103	\N	\N	\N	\N	\N	\N
144	1	1	170	2	104	\N	\N	\N	\N	\N	\N
146	1	1	170	2	105	\N	\N	\N	\N	\N	\N
145	2	1.25	420	1	105	\N	100	\N	\N	\N	\N
147	4	4	680	2	106	\N	160	\N	2025-08-19 11:59:13	\N	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4
148	1	0.54	99	6	107	\N	0	2025-08-19 11:44:30	2025-08-19 11:58:56	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4
186	1	0.83333333333333	140	3	132	\N	33.33	2025-08-24 14:00:50	\N	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4	\N
187	1	1	170	2	133	\N	40	2025-08-25 15:22:32	\N	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4	\N
149	1	0.7	94.5	5	108	\N	31.5	2025-08-19 14:11:14	2025-08-19 17:54:53	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4
150	1	1.25	210	1	109	\N	50	2025-08-20 10:11:15	\N	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4	\N
188	1	0.5	140	3	133	\N	33.33	2025-08-25 15:22:32	\N	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4	\N
189	2	2.3	420	1	134	\N	100	2025-08-30 17:12:05	\N	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4	\N
190	2	2	340	2	134	\N	80	2025-08-30 17:12:05	\N	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4	\N
191	2	2.3	420	1	135	\N	100	2025-08-30 17:13:04	\N	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4	\N
192	2	2	340	2	135	\N	80	2025-08-30 17:13:04	\N	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4	\N
193	1	1.25	210	1	136	\N	50	2025-08-30 17:16:38	\N	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4	\N
194	1	0.83333333333333	140	3	136	\N	33.33	2025-08-30 17:16:38	\N	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4	\N
195	2	1	340	2	136	\N	80	2025-08-30 17:16:38	\N	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4	\N
197	1	1.2	132	6	138	\N	0	2025-09-04 11:12:04	2025-09-04 12:06:39	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4
151	2	2.3	420	1	110	\N	100	2025-08-20 10:20:06	2025-08-20 10:54:41	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4
152	1	1	170	2	110	\N	40	2025-08-20 10:20:06	2025-08-20 10:54:41	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4
153	1	1.15	210	7	111	\N	50	2025-08-20 16:14:16	2025-08-20 16:15:34	1f07dcbe-c3eb-6520-b05d-9b2e91069048	1f07dcbe-c3eb-6520-b05d-9b2e91069048
154	2	2	340	2	112	\N	80	2025-08-22 13:32:19	\N	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4	\N
155	2	1.25	420	1	113	\N	100	2025-08-22 13:35:06	\N	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4	\N
156	1	1.25	210	1	113	\N	50	2025-08-22 13:35:06	\N	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4	\N
157	2	2.3	420	1	114	\N	100	2025-08-22 13:38:19	\N	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4	\N
158	1	1	170	2	114	\N	40	2025-08-22 13:38:19	\N	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4	\N
159	2	2	340	2	115	\N	80	2025-08-22 13:43:12	\N	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4	\N
160	2	1.4	280	3	115	\N	66.66	2025-08-22 13:43:12	\N	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4	\N
161	2	1.25	420	1	116	\N	100	2025-08-22 13:47:55	\N	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4	\N
162	2	1.25	420	1	116	\N	100	2025-08-22 13:47:55	\N	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4	\N
163	4	5	840	1	117	\N	200	2025-08-22 13:52:41	\N	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4	\N
164	1	1.15	210	1	118	\N	50	2025-08-22 13:55:53	\N	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4	\N
165	2	2	340	2	118	\N	80	2025-08-22 13:55:53	\N	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4	\N
166	1	0.5	140	3	118	\N	33.33	2025-08-22 13:55:53	\N	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4	\N
167	2	2.3	420	1	119	\N	100	2025-08-22 13:57:03	\N	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4	\N
168	2	2.3	420	1	120	\N	100	2025-08-22 14:03:14	\N	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4	\N
169	1	1	170	2	120	\N	40	2025-08-22 14:03:14	\N	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4	\N
170	1	0.5	140	3	120	\N	33.33	2025-08-22 14:03:14	\N	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4	\N
171	4	4	680	2	121	\N	160	2025-08-23 14:01:06	\N	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4	\N
172	1	1	170	2	122	\N	40	2025-08-23 14:02:07	\N	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4	\N
173	1	1.15	210	1	122	\N	50	2025-08-23 14:02:07	\N	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4	\N
174	4	4	680	2	123	\N	160	2025-08-23 14:13:14	\N	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4	\N
175	2	2.3	420	1	124	\N	100	2025-08-23 14:14:20	\N	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4	\N
176	1	1.15	210	1	124	\N	50	2025-08-23 14:14:20	\N	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4	\N
177	4	4	680	2	125	\N	160	2025-08-23 14:18:57	\N	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4	\N
178	1	1.15	210	1	126	\N	50	2025-08-23 14:58:52	\N	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4	\N
179	1	1.25	210	1	127	\N	50	2025-08-23 14:59:15	\N	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4	\N
180	1	1.25	210	1	128	\N	50	2025-08-24 05:51:19	\N	1f024e74-ad22-6808-a0cc-9b7b2ff302fc	\N
181	1	1.15	210	1	129	\N	50	2025-08-24 13:56:10	\N	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4	\N
182	1	1	170	2	129	\N	40	2025-08-24 13:56:10	\N	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4	\N
183	1	0.83333333333333	140	3	130	\N	33.33	2025-08-24 13:57:24	\N	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4	\N
184	1	1.15	210	1	131	\N	50	2025-08-24 13:59:44	\N	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4	\N
185	1	1	170	2	131	\N	40	2025-08-24 13:59:44	\N	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4	\N
198	2	2	340	2	139	\N	80	2025-09-04 12:10:20	\N	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4	\N
199	1	1.15	210	1	139	\N	50	2025-09-04 12:10:20	\N	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4	\N
200	1	1.15	210	1	140	\N	50	2025-09-04 17:58:54	\N	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4	\N
201	2	2	340	2	140	\N	80	2025-09-04 17:58:54	\N	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4	\N
202	1	1.15	210	1	141	\N	50	2025-09-05 15:38:33	\N	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4	\N
203	1	1	170	2	141	\N	40	2025-09-05 15:38:33	\N	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4	\N
204	2	2.3	420	1	142	\N	100	2025-09-05 15:43:11	\N	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4	\N
205	1	0.5	140	3	142	\N	33.33	2025-09-05 15:43:11	\N	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4	\N
206	1	1.15	210	1	143	\N	50	2025-09-05 15:44:33	\N	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4	\N
207	1	1	170	2	143	\N	40	2025-09-05 15:44:33	\N	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4	\N
208	3	3	510	2	144	\N	120	2025-09-11 13:07:45	\N	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4	\N
209	1	1.15	210	1	145	\N	50	2025-09-12 07:48:52	\N	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4	\N
210	1	1	170	2	145	\N	40	2025-09-12 07:48:52	\N	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4	\N
211	1	1.15	210	1	146	\N	50	2025-09-14 13:15:15	\N	1f08e51e-d4bb-6e5e-ac48-c9cbe02f0cce	\N
212	2	2	340	2	146	\N	80	2025-09-14 13:15:15	\N	1f08e51e-d4bb-6e5e-ac48-c9cbe02f0cce	\N
\.


--
-- Data for Name: landing; Type: TABLE DATA; Schema: public; Owner: app
--

COPY public.landing (id, airport_code, airport_name, touches, complets, vol_id) FROM stdin;
1	\N	\N	6	1	\N
2	FMEE	Roland-Garros	3	0	\N
3	FMEP	Pierrefonds	0	1	\N
4	FMEP	Pierrefonds	0	1	\N
5	FMEE	Roland-Garros	3	0	\N
6	FMEP	Pierrefonds	0	1	\N
7	FMEE	Roland-Garros	2	0	\N
8	FMEE	Roland-Garros	3	0	\N
9	FMEP	Pierrefonds	0	1	\N
10	FMEE	Roland-Garros	2	0	\N
11	FMEP	Pierrefonds	0	1	\N
12	FMEE	Roland-Garros	2	0	\N
13	FMEP	Pierrefonds	0	1	\N
14	FMEP	Pierrefonds	0	0	\N
15	FMEE	Roland-Garros	2	1	\N
17	FMEE	Roland-Garros	2	0	\N
16	FMEP	Pierrefonds	0	1	\N
18	FMEE	Roland-Garros	2	0	\N
19	FMEP	Pierrefonds	0	1	\N
20	FMEP	Pierrefonds	0	1	\N
21	FMEE	Roland-Garros	2	0	\N
22	FMEP	Pierrefonds	0	1	\N
23	FMEE	Roland-Garros	2	0	\N
24	FMEP	Pierrefonds	0	1	\N
25	FMEE	Roland-Garros	2	0	\N
26	FMEE	Roland-Garros	2	0	\N
27	FMEP	Pierrefonds	0	1	\N
28	FMEE	Roland-Garros	2	0	\N
29	FMEP	Pierrefonds	0	1	\N
30	FMEP	Pierrefonds	0	1	\N
31	FMEE	Roland-Garros	2	0	\N
32	FMEP	Pierrefonds	0	1	\N
33	FMEE	Roland-Garros	2	0	\N
34	FMEP	Pierrefonds	0	1	\N
35	FMEE	Roland-Garros	2	0	\N
36	FMEE	Roland-Garros	2	0	\N
37	FMEP	Pierrefonds	0	1	\N
38	FMEE	Roland-Garros	2	0	\N
39	FMEP	Pierrefonds	0	1	\N
40	FMEP	Pierrefonds	0	1	\N
41	FMEE	Roland-Garros	2	0	\N
42	FMEP	Pierrefonds	0	1	\N
43	FMEE	Roland-Garros	2	0	\N
44	FMEP	Pierrefonds	0	1	\N
45	FMEP	Pierrefonds	0	1	\N
46	\N	\N	0	1	\N
47	\N	\N	2	0	\N
50	FMEP	Pierrefonds	3	1	133
49	\N	\N	2	0	\N
48	\N	\N	0	1	\N
51	\N	\N	0	0	\N
52	\N	\N	0	0	\N
53	\N	\N	0	0	\N
54	\N	\N	0	0	\N
55	\N	\N	0	0	\N
56	\N	\N	0	0	\N
57	\N	\N	0	0	\N
58	\N	\N	0	0	\N
60	FMEE	Roland-Garros	2	0	129
59	FMEP	Pierrefonds	0	1	129
61	FMEP	Pierrefonds	0	1	130
62	FMEP	Pierrefonds	0	1	134
63	FMEE	Roland-Garros	5	0	134
64	FMEP	Pierrefonds	1	1	135
65	FMEE	Roland-Garros	8	0	135
66	FMEP	Pierrefonds	0	1	131
67	FMEP	Pierrefonds	0	1	132
68	FMEP	Pierrefonds	0	1	137
69	FMEP	Pierrefonds	0	1	138
70	FMEP	Pierrefonds	0	1	139
71	FMEP	Pierrefonds	0	1	140
73	FMEP	Pierrefonds	0	1	142
75	FMEP	Pierrefonds	0	1	143
74	FMEP	Pierrefonds	0	1	\N
76	FMEP	Pierrefonds	0	1	144
78	FMEP	Pierrefonds	0	1	146
92	FMEP	Pierrefonds	0	1	\N
104	FMEP	Pierrefonds	0	2	154
93	FMEP	Pierrefonds	0	1	149
94	FMEP	Pierrefonds	0	1	150
77	FMEP	Pierrefonds	0	2	145
79	FMEP	Pierrefonds	0	4	\N
80	FMEP	Pierrefonds	0	5	\N
81	FMEP	Pierrefonds	0	4	\N
82	FMEP	Pierrefonds	0	5	\N
83	FMEP	Pierrefonds	0	5	\N
84	FMEP	Pierrefonds	0	4	\N
85	FMEP	Pierrefonds	0	4	\N
86	FMEP	Pierrefonds	0	4	\N
88	FMEP	Pierrefonds	2	1	148
89	FMEP	Pierrefonds	0	4	147
87	FMEP	Pierrefonds	0	4	\N
90	FMEP	Pierrefonds	0	1	\N
91	FMEP	Pierrefonds	0	1	\N
95	FMEP	Pierrefonds	0	2	\N
96	FMEP	Pierrefonds	0	1	\N
97	FMEP	Pierrefonds	0	2	\N
98	FMEP	Pierrefonds	0	1	\N
102	FMEP	Pierrefonds	0	1	152
99	FMEP	Pierrefonds	0	2	\N
100	FMEP	Pierrefonds	0	1	\N
105	FMEP	Pierrefonds	0	2	155
106	FMEP	Pierrefonds	0	1	156
107	FMEP	Pierrefonds	0	2	157
108	FMEP	Pierrefonds	0	1	158
109	FMEP	Pierrefonds	0	2	159
101	FMEP	Pierrefonds	0	2	151
110	FMEP	Pierrefonds	0	2	160
103	FMEP	Pierrefonds	4	1	153
111	FMEP	Pierrefonds	0	2	161
112	FMEP	Pierrefonds	0	2	162
113	FMEP	Pierrefonds	0	4	163
114	FMEP	Pierrefonds	0	1	164
115	FMEP	Pierrefonds	0	2	165
116	FMEP	Pierrefonds	0	1	166
117	FMEP	Pierrefonds	0	2	167
118	FMEP	Pierrefonds	0	2	168
119	FMEP	Pierrefonds	0	1	169
120	FMEP	Pierrefonds	0	1	170
121	FMEP	Pierrefonds	0	4	171
122	FMEP	Pierrefonds	0	1	172
123	FMEP	Pierrefonds	0	1	173
124	FMEP	Pierrefonds	0	4	174
125	FMEP	Pierrefonds	0	2	175
126	FMEP	Pierrefonds	0	1	176
127	FMEP	Pierrefonds	0	4	177
128	FMEP	Pierrefonds	0	1	178
129	FMEP	Pierrefonds	0	1	179
130	FMEP	Pierrefonds	0	1	180
131	FMEP	Pierrefonds	0	1	181
132	FMEP	Pierrefonds	0	1	182
133	FMEP	Pierrefonds	0	1	183
134	FMEP	Pierrefonds	0	1	184
135	FMEP	Pierrefonds	0	1	185
136	FMEP	Pierrefonds	0	1	186
137	FMEP	Pierrefonds	0	1	187
138	FMEP	Pierrefonds	0	1	188
139	FMEP	Pierrefonds	0	2	189
140	FMEP	Pierrefonds	0	2	190
141	FMEP	Pierrefonds	0	2	191
142	FMEP	Pierrefonds	0	2	192
143	FMEP	Pierrefonds	0	1	193
144	FMEP	Pierrefonds	0	1	194
145	FMEP	Pierrefonds	0	2	195
146	/airports/5	Bras-Panon	4	0	197
147	FMEP	FMEP Pierrefonds	0	1	197
148	FMEP	FMEP Pierrefonds	0	2	198
149	FMEP	FMEP Pierrefonds	0	1	199
150	FMEP	FMEP Pierrefonds	0	1	200
151	FMEP	FMEP Pierrefonds	0	2	201
152	FMEP	FMEP Pierrefonds	0	1	202
153	FMEP	FMEP Pierrefonds	0	1	203
154	FMEP	FMEP Pierrefonds	0	2	204
155	FMEP	FMEP Pierrefonds	0	1	205
156	FMEP	FMEP Pierrefonds	0	1	206
157	FMEP	FMEP Pierrefonds	0	1	207
158	FMEP	FMEP Pierrefonds	0	3	208
159	FMEP	FMEP Pierrefonds	0	1	209
160	FMEP	FMEP Pierrefonds	0	1	210
161	FMEP	FMEP Pierrefonds	0	1	211
162	FMEP	FMEP Pierrefonds	0	2	212
\.


--
-- Data for Name: passager; Type: TABLE DATA; Schema: public; Owner: app
--

-- COPY public.passager (id, nom, prenom, email, telephone, date, consent_accepted, consent_text, consent_datetime) FROM stdin;
-- 60	Maillot	Sébastien	sebastien.maillot@gmx.fr	0692406298	2025-09-05 00:00:00	t	Je reconnais avoir pris connaissance du fait que les exigences applicables aux vols en ULM ne garantissent pas un niveau de sécurité aussi élevé que les vols commerciaux de l'aviation certifiée.\nL'ULM, le pilote et l'exploitant ne sont pas soumis à des opérations de contrôle préalables de la part de l'autorité.	2025-09-05 13:18:11
-- 63	Germain	Bernadette	m_seb@icloud.com	0692406298	2025-09-05 00:00:00	t	Je reconnais avoir pris connaissance du fait que les exigences applicables aux vols en ULM ne garantissent pas un niveau de sécurité aussi élevé que les vols commerciaux de l'aviation certifiée.\nL'ULM, le pilote et l'exploitant ne sont pas soumis à des opérations de contrôle préalables de la part de l'autorité.	2025-09-05 13:30:57
-- 64	Dijoux	Roseline	m_seb@icloud.com	0692406298	2025-09-05 00:00:00	t	Je reconnais avoir pris connaissance du fait que les exigences applicables aux vols en ULM ne garantissent pas un niveau de sécurité aussi élevé que les vols commerciaux de l'aviation certifiée.\nL'ULM, le pilote et l'exploitant ne sont pas soumis à des opérations de contrôle préalables de la part de l'autorité.	2025-09-05 13:32:32
-- 1	Maillot	Sébastien	sebastien.maillot@gmx.fr	0692406298	2025-03-11 00:00:00	t	Je reconnais avoir pris connaissance du fait que les exigences applicables aux vols en ULM ne garantissent pas un niveau de sécurité aussi élevé que les vols commerciaux de l'aviation certifiée.\nL'ULM, le pilote et l'exploitant ne sont pas soumis à des opérations de contrôle préalables de la part de l'autorité. 	2025-03-11 00:00:00
-- 2	Bouilloux	Louise	sebastien.maillot@gmx.fr	0692299004	2025-03-11 00:00:00	t	Je reconnais avoir pris connaissance du fait que les exigences applicables aux vols en ULM ne garantissent pas un niveau de sécurité aussi élevé que les vols commerciaux de l'aviation certifiée.\nL'ULM, le pilote et l'exploitant ne sont pas soumis à des opérations de contrôle préalables de la part de l'autorité. 	2025-03-11 00:00:00
-- 3	Sebastien	Maillot	sebastien.maillot@gmx.fr	0692406298	2025-03-21 00:00:00	t	Je reconnais avoir pris connaissance du fait que les exigences applicables aux vols en ULM ne garantissent pas un niveau de sécurité aussi élevé que les vols commerciaux de l'aviation certifiée.\nL'ULM, le pilote et l'exploitant ne sont pas soumis à des opérations de contrôle préalables de la part de l'autorité. 	2025-03-21 00:00:00
-- 4	poupin	chantal	christian.poupin0@orange.fr	0785531101	2025-03-26 00:00:00	t	Je reconnais avoir pris connaissance du fait que les exigences applicables aux vols en ULM ne garantissent pas un niveau de sécurité aussi élevé que les vols commerciaux de l'aviation certifiée.\nL'ULM, le pilote et l'exploitant ne sont pas soumis à des opérations de contrôle préalables de la part de l'autorité. 	2025-03-26 00:00:00
-- 5	bertho	liliane	berthoavb@orange.fr	0685650780	2025-03-26 00:00:00	t	Je reconnais avoir pris connaissance du fait que les exigences applicables aux vols en ULM ne garantissent pas un niveau de sécurité aussi élevé que les vols commerciaux de l'aviation certifiée.\nL'ULM, le pilote et l'exploitant ne sont pas soumis à des opérations de contrôle préalables de la part de l'autorité. 	2025-03-26 00:00:00
-- 6	Pogorelis 	Robertas	rpogorelis@yahoo.com	+37068953306	2025-03-28 00:00:00	t	Je reconnais avoir pris connaissance du fait que les exigences applicables aux vols en ULM ne garantissent pas un niveau de sécurité aussi élevé que les vols commerciaux de l'aviation certifiée.\nL'ULM, le pilote et l'exploitant ne sont pas soumis à des opérations de contrôle préalables de la part de l'autorité. 	2025-03-28 00:00:00
-- 7	Skirkaite	Sigita	sigitaskirkaite@gmail.com	+37062877768	2025-03-28 00:00:00	t	Je reconnais avoir pris connaissance du fait que les exigences applicables aux vols en ULM ne garantissent pas un niveau de sécurité aussi élevé que les vols commerciaux de l'aviation certifiée.\nL'ULM, le pilote et l'exploitant ne sont pas soumis à des opérations de contrôle préalables de la part de l'autorité. 	2025-03-28 00:00:00
-- 8	MASSON	patrick	patmas0968@gmail.com	0664657592	2025-03-30 00:00:00	t	Je reconnais avoir pris connaissance du fait que les exigences applicables aux vols en ULM ne garantissent pas un niveau de sécurité aussi élevé que les vols commerciaux de l'aviation certifiée.\nL'ULM, le pilote et l'exploitant ne sont pas soumis à des opérations de contrôle préalables de la part de l'autorité. 	2025-03-30 00:00:00
-- 9	MASSON	pascale	pascale.masson05@numericable.fr	0664040505	2025-03-30 00:00:00	t	Je reconnais avoir pris connaissance du fait que les exigences applicables aux vols en ULM ne garantissent pas un niveau de sécurité aussi élevé que les vols commerciaux de l'aviation certifiée.\nL'ULM, le pilote et l'exploitant ne sont pas soumis à des opérations de contrôle préalables de la part de l'autorité. 	2025-03-30 00:00:00
-- 10	fezas	justine	justinefzs@gmail.com	0640641742	2025-03-31 00:00:00	t	Je reconnais avoir pris connaissance du fait que les exigences applicables aux vols en ULM ne garantissent pas un niveau de sécurité aussi élevé que les vols commerciaux de l'aviation certifiée.\nL'ULM, le pilote et l'exploitant ne sont pas soumis à des opérations de contrôle préalables de la part de l'autorité. 	2025-03-31 00:00:00
-- 11	Sazerac	Angélique	angelique.sazerac@icloud.com	+262692425168	2025-04-01 00:00:00	t	Je reconnais avoir pris connaissance du fait que les exigences applicables aux vols en ULM ne garantissent pas un niveau de sécurité aussi élevé que les vols commerciaux de l'aviation certifiée.\nL'ULM, le pilote et l'exploitant ne sont pas soumis à des opérations de contrôle préalables de la part de l'autorité. 	2025-04-01 00:00:00
-- 12	pillai	praveen	praveenpillai9@hotmail.com	+919886028600	2025-04-01 00:00:00	t	Je reconnais avoir pris connaissance du fait que les exigences applicables aux vols en ULM ne garantissent pas un niveau de sécurité aussi élevé que les vols commerciaux de l'aviation certifiée.\nL'ULM, le pilote et l'exploitant ne sont pas soumis à des opérations de contrôle préalables de la part de l'autorité. 	2025-04-01 00:00:00
-- 13	bole	nidhi	nidhibole@yahoo.com	+919980030107	2025-04-01 00:00:00	t	Je reconnais avoir pris connaissance du fait que les exigences applicables aux vols en ULM ne garantissent pas un niveau de sécurité aussi élevé que les vols commerciaux de l'aviation certifiée.\nL'ULM, le pilote et l'exploitant ne sont pas soumis à des opérations de contrôle préalables de la part de l'autorité. 	2025-04-01 00:00:00
-- 14	coeurdevey	amelie	amelie.coeurdevey@outlook.fr	0633302342	2025-04-09 00:00:00	t	Je reconnais avoir pris connaissance du fait que les exigences applicables aux vols en ULM ne garantissent pas un niveau de sécurité aussi élevé que les vols commerciaux de l'aviation certifiée.\nL'ULM, le pilote et l'exploitant ne sont pas soumis à des opérations de contrôle préalables de la part de l'autorité. 	2025-04-09 00:00:00
-- 15	chagrot	jerome	jerome.chagrot@orange.fr	0791140771	2025-04-09 00:00:00	t	Je reconnais avoir pris connaissance du fait que les exigences applicables aux vols en ULM ne garantissent pas un niveau de sécurité aussi élevé que les vols commerciaux de l'aviation certifiée.\nL'ULM, le pilote et l'exploitant ne sont pas soumis à des opérations de contrôle préalables de la part de l'autorité. 	2025-04-09 00:00:00
-- 16	Abbas turki	Amel	latelier2amel@gmail.com	0620935830	2025-04-09 00:00:00	t	Je reconnais avoir pris connaissance du fait que les exigences applicables aux vols en ULM ne garantissent pas un niveau de sécurité aussi élevé que les vols commerciaux de l'aviation certifiée.\nL'ULM, le pilote et l'exploitant ne sont pas soumis à des opérations de contrôle préalables de la part de l'autorité. 	2025-04-09 00:00:00
-- 17	broy	julie	julie.broy@yahoo.fr	0679195906	2025-04-09 00:00:00	t	Je reconnais avoir pris connaissance du fait que les exigences applicables aux vols en ULM ne garantissent pas un niveau de sécurité aussi élevé que les vols commerciaux de l'aviation certifiée.\nL'ULM, le pilote et l'exploitant ne sont pas soumis à des opérations de contrôle préalables de la part de l'autorité. 	2025-04-09 00:00:00
-- 18	lebon	fabienne	fabienne-l.lebon@outlook.fr	0693928749	2025-04-10 00:00:00	t	Je reconnais avoir pris connaissance du fait que les exigences applicables aux vols en ULM ne garantissent pas un niveau de sécurité aussi élevé que les vols commerciaux de l'aviation certifiée.\nL'ULM, le pilote et l'exploitant ne sont pas soumis à des opérations de contrôle préalables de la part de l'autorité. 	2025-04-10 00:00:00
-- 19	larcher	rené 	lajoe88@hotmail.com	0671662951	2025-04-11 00:00:00	t	Je reconnais avoir pris connaissance du fait que les exigences applicables aux vols en ULM ne garantissent pas un niveau de sécurité aussi élevé que les vols commerciaux de l'aviation certifiée.\nL'ULM, le pilote et l'exploitant ne sont pas soumis à des opérations de contrôle préalables de la part de l'autorité. 	2025-04-11 00:00:00
-- 20	Kupper	Christina	christina.kupper@web.de	0491727734376	2025-04-11 00:00:00	t	Je reconnais avoir pris connaissance du fait que les exigences applicables aux vols en ULM ne garantissent pas un niveau de sécurité aussi élevé que les vols commerciaux de l'aviation certifiée.\nL'ULM, le pilote et l'exploitant ne sont pas soumis à des opérations de contrôle préalables de la part de l'autorité. 	2025-04-11 00:00:00
-- 21	requier	alexandre 	estelle.a2@wanadoo.fr	0677134673	2025-04-11 00:00:00	t	Je reconnais avoir pris connaissance du fait que les exigences applicables aux vols en ULM ne garantissent pas un niveau de sécurité aussi élevé que les vols commerciaux de l'aviation certifiée.\nL'ULM, le pilote et l'exploitant ne sont pas soumis à des opérations de contrôle préalables de la part de l'autorité. 	2025-04-11 00:00:00
-- 22	lebrun	estelle	estelle.a2@wanadoo.fr	0677134673	2025-04-11 00:00:00	t	Je reconnais avoir pris connaissance du fait que les exigences applicables aux vols en ULM ne garantissent pas un niveau de sécurité aussi élevé que les vols commerciaux de l'aviation certifiée.\nL'ULM, le pilote et l'exploitant ne sont pas soumis à des opérations de contrôle préalables de la part de l'autorité. 	2025-04-11 00:00:00
-- 23	requier 	augustin	estelle.a2@wanadoo.fr	0677134673	2025-04-11 00:00:00	t	Je reconnais avoir pris connaissance du fait que les exigences applicables aux vols en ULM ne garantissent pas un niveau de sécurité aussi élevé que les vols commerciaux de l'aviation certifiée.\nL'ULM, le pilote et l'exploitant ne sont pas soumis à des opérations de contrôle préalables de la part de l'autorité. 	2025-04-11 00:00:00
-- 24	GUTIERREZ	Justine	justine.gutierrez@outlook.fr	0642501785	2025-04-11 00:00:00	t	Je reconnais avoir pris connaissance du fait que les exigences applicables aux vols en ULM ne garantissent pas un niveau de sécurité aussi élevé que les vols commerciaux de l'aviation certifiée.\nL'ULM, le pilote et l'exploitant ne sont pas soumis à des opérations de contrôle préalables de la part de l'autorité. 	2025-04-11 00:00:00
-- 25	Veynachter	Thomas	thomas.veynachter@gmail.com	0625745867	2025-04-11 00:00:00	t	Je reconnais avoir pris connaissance du fait que les exigences applicables aux vols en ULM ne garantissent pas un niveau de sécurité aussi élevé que les vols commerciaux de l'aviation certifiée.\nL'ULM, le pilote et l'exploitant ne sont pas soumis à des opérations de contrôle préalables de la part de l'autorité. 	2025-04-11 00:00:00
-- 26	GIL GARGAN	Alba	elcorreodepaquetes@gmail.com	+34636708141	2025-04-14 00:00:00	t	Je reconnais avoir pris connaissance du fait que les exigences applicables aux vols en ULM ne garantissent pas un niveau de sécurité aussi élevé que les vols commerciaux de l'aviation certifiée.\nL'ULM, le pilote et l'exploitant ne sont pas soumis à des opérations de contrôle préalables de la part de l'autorité. 	2025-04-14 00:00:00
-- 27	chapot	antoine	antoinechapot@gmail.com	0693927688	2025-04-14 00:00:00	t	Je reconnais avoir pris connaissance du fait que les exigences applicables aux vols en ULM ne garantissent pas un niveau de sécurité aussi élevé que les vols commerciaux de l'aviation certifiée.\nL'ULM, le pilote et l'exploitant ne sont pas soumis à des opérations de contrôle préalables de la part de l'autorité. 	2025-04-14 00:00:00
-- 28	chapot	jean	jean.chapot@sfr.fr	0666158066	2025-04-14 00:00:00	t	Je reconnais avoir pris connaissance du fait que les exigences applicables aux vols en ULM ne garantissent pas un niveau de sécurité aussi élevé que les vols commerciaux de l'aviation certifiée.\nL'ULM, le pilote et l'exploitant ne sont pas soumis à des opérations de contrôle préalables de la part de l'autorité. 	2025-04-14 00:00:00
-- 29	mouton	orianne	orianne.mouton@gmail.com	0624003327	2025-04-14 00:00:00	t	Je reconnais avoir pris connaissance du fait que les exigences applicables aux vols en ULM ne garantissent pas un niveau de sécurité aussi élevé que les vols commerciaux de l'aviation certifiée.\nL'ULM, le pilote et l'exploitant ne sont pas soumis à des opérations de contrôle préalables de la part de l'autorité. 	2025-04-14 00:00:00
-- 30	bailliet	adrien	adrien-bailliet@live.fr	0631152402	2025-04-14 00:00:00	t	Je reconnais avoir pris connaissance du fait que les exigences applicables aux vols en ULM ne garantissent pas un niveau de sécurité aussi élevé que les vols commerciaux de l'aviation certifiée.\nL'ULM, le pilote et l'exploitant ne sont pas soumis à des opérations de contrôle préalables de la part de l'autorité. 	2025-04-14 00:00:00
-- 31	haudiquet	nicolas	nh@mbhavocats.fr	0677181654	2025-04-14 00:00:00	t	Je reconnais avoir pris connaissance du fait que les exigences applicables aux vols en ULM ne garantissent pas un niveau de sécurité aussi élevé que les vols commerciaux de l'aviation certifiée.\nL'ULM, le pilote et l'exploitant ne sont pas soumis à des opérations de contrôle préalables de la part de l'autorité. 	2025-04-14 00:00:00
-- 32	cuyle	brigitte	brigit.cuyle@orange.fr	0674340946	2025-04-14 00:00:00	t	Je reconnais avoir pris connaissance du fait que les exigences applicables aux vols en ULM ne garantissent pas un niveau de sécurité aussi élevé que les vols commerciaux de l'aviation certifiée.\nL'ULM, le pilote et l'exploitant ne sont pas soumis à des opérations de contrôle préalables de la part de l'autorité. 	2025-04-14 00:00:00
-- 33	PROTO	Marine	marine.proto@gmail.com	0782834316	2025-04-14 00:00:00	t	Je reconnais avoir pris connaissance du fait que les exigences applicables aux vols en ULM ne garantissent pas un niveau de sécurité aussi élevé que les vols commerciaux de l'aviation certifiée.\nL'ULM, le pilote et l'exploitant ne sont pas soumis à des opérations de contrôle préalables de la part de l'autorité. 	2025-04-14 00:00:00
-- 34	Hoarau	David	hoarau13820@gmail.com	0669233506	2025-04-14 00:00:00	t	Je reconnais avoir pris connaissance du fait que les exigences applicables aux vols en ULM ne garantissent pas un niveau de sécurité aussi élevé que les vols commerciaux de l'aviation certifiée.\nL'ULM, le pilote et l'exploitant ne sont pas soumis à des opérations de contrôle préalables de la part de l'autorité. 	2025-04-14 00:00:00
-- 35	Diris	Paul	pauldiris1@gmail.com	0641527370	2025-04-15 00:00:00	t	Je reconnais avoir pris connaissance du fait que les exigences applicables aux vols en ULM ne garantissent pas un niveau de sécurité aussi élevé que les vols commerciaux de l'aviation certifiée.\nL'ULM, le pilote et l'exploitant ne sont pas soumis à des opérations de contrôle préalables de la part de l'autorité. 	2025-04-15 00:00:00
-- 36	fortuné	gaspard	gagafoot@gmail.com	0667833298	2025-04-15 00:00:00	t	Je reconnais avoir pris connaissance du fait que les exigences applicables aux vols en ULM ne garantissent pas un niveau de sécurité aussi élevé que les vols commerciaux de l'aviation certifiée.\nL'ULM, le pilote et l'exploitant ne sont pas soumis à des opérations de contrôle préalables de la part de l'autorité. 	2025-04-15 00:00:00
-- 37	dupuis	alex	alex.dupuis33@orange.fr	0635974151	2025-04-15 00:00:00	t	Je reconnais avoir pris connaissance du fait que les exigences applicables aux vols en ULM ne garantissent pas un niveau de sécurité aussi élevé que les vols commerciaux de l'aviation certifiée.\nL'ULM, le pilote et l'exploitant ne sont pas soumis à des opérations de contrôle préalables de la part de l'autorité. 	2025-04-15 00:00:00
-- 38	chapelle	bernard	annemariebilou@yahoo.fr	0781574287	2025-04-15 00:00:00	t	Je reconnais avoir pris connaissance du fait que les exigences applicables aux vols en ULM ne garantissent pas un niveau de sécurité aussi élevé que les vols commerciaux de l'aviation certifiée.\nL'ULM, le pilote et l'exploitant ne sont pas soumis à des opérations de contrôle préalables de la part de l'autorité. 	2025-04-15 00:00:00
-- 39	chapelle	annemarie	annemariebilou@yahoo.fr	0781213065	2025-04-15 00:00:00	t	Je reconnais avoir pris connaissance du fait que les exigences applicables aux vols en ULM ne garantissent pas un niveau de sécurité aussi élevé que les vols commerciaux de l'aviation certifiée.\nL'ULM, le pilote et l'exploitant ne sont pas soumis à des opérations de contrôle préalables de la part de l'autorité. 	2025-04-15 00:00:00
-- 40	levacher	alain	levacher.alain@laposte.net	0648142776	2025-04-16 00:00:00	t	Je reconnais avoir pris connaissance du fait que les exigences applicables aux vols en ULM ne garantissent pas un niveau de sécurité aussi élevé que les vols commerciaux de l'aviation certifiée.\nL'ULM, le pilote et l'exploitant ne sont pas soumis à des opérations de contrôle préalables de la part de l'autorité. 	2025-04-16 00:00:00
-- 41	rault	loic	loicrault50@gmail.fr	0677796445	2025-04-16 00:00:00	t	Je reconnais avoir pris connaissance du fait que les exigences applicables aux vols en ULM ne garantissent pas un niveau de sécurité aussi élevé que les vols commerciaux de l'aviation certifiée.\nL'ULM, le pilote et l'exploitant ne sont pas soumis à des opérations de contrôle préalables de la part de l'autorité. 	2025-04-16 00:00:00
-- 42	robert	malodobry	robert.malodobry@gmail.com	+48662062505	2025-04-16 00:00:00	t	Je reconnais avoir pris connaissance du fait que les exigences applicables aux vols en ULM ne garantissent pas un niveau de sécurité aussi élevé que les vols commerciaux de l'aviation certifiée.\nL'ULM, le pilote et l'exploitant ne sont pas soumis à des opérations de contrôle préalables de la part de l'autorité. 	2025-04-16 00:00:00
-- 43	guimier	christine	christine.guimier@sfr.fr	0622897082	2025-04-18 00:00:00	t	Je reconnais avoir pris connaissance du fait que les exigences applicables aux vols en ULM ne garantissent pas un niveau de sécurité aussi élevé que les vols commerciaux de l'aviation certifiée.\nL'ULM, le pilote et l'exploitant ne sont pas soumis à des opérations de contrôle préalables de la part de l'autorité. 	2025-04-18 00:00:00
-- 44	Guimier	dominique	dominique.guimier@sfr.fr	0617714000	2025-04-18 00:00:00	t	Je reconnais avoir pris connaissance du fait que les exigences applicables aux vols en ULM ne garantissent pas un niveau de sécurité aussi élevé que les vols commerciaux de l'aviation certifiée.\nL'ULM, le pilote et l'exploitant ne sont pas soumis à des opérations de contrôle préalables de la part de l'autorité. 	2025-04-18 00:00:00
-- 45	luis	sagastume	sagastume_@hotmail.com	0262667947508	2025-04-19 00:00:00	t	Je reconnais avoir pris connaissance du fait que les exigences applicables aux vols en ULM ne garantissent pas un niveau de sécurité aussi élevé que les vols commerciaux de l'aviation certifiée.\nL'ULM, le pilote et l'exploitant ne sont pas soumis à des opérations de contrôle préalables de la part de l'autorité. 	2025-04-19 00:00:00
-- 46	nerea	garate	politteskaerak@gmail.com	0262673104545	2025-04-19 00:00:00	t	Je reconnais avoir pris connaissance du fait que les exigences applicables aux vols en ULM ne garantissent pas un niveau de sécurité aussi élevé que les vols commerciaux de l'aviation certifiée.\nL'ULM, le pilote et l'exploitant ne sont pas soumis à des opérations de contrôle préalables de la part de l'autorité. 	2025-04-19 00:00:00
-- 47	roche	clara	roche.clara42@gmail.com	0693842898	2025-04-19 00:00:00	t	Je reconnais avoir pris connaissance du fait que les exigences applicables aux vols en ULM ne garantissent pas un niveau de sécurité aussi élevé que les vols commerciaux de l'aviation certifiée.\nL'ULM, le pilote et l'exploitant ne sont pas soumis à des opérations de contrôle préalables de la part de l'autorité. 	2025-04-19 00:00:00
-- 48	fernandez	cedric	cedric-fernandez@hotmail.fr	0673712011	2025-04-19 00:00:00	t	Je reconnais avoir pris connaissance du fait que les exigences applicables aux vols en ULM ne garantissent pas un niveau de sécurité aussi élevé que les vols commerciaux de l'aviation certifiée.\nL'ULM, le pilote et l'exploitant ne sont pas soumis à des opérations de contrôle préalables de la part de l'autorité. 	2025-04-19 00:00:00
-- 49	caffin	aurelie	liline1901@hotmail.fr	0622414064	2025-04-19 00:00:00	t	Je reconnais avoir pris connaissance du fait que les exigences applicables aux vols en ULM ne garantissent pas un niveau de sécurité aussi élevé que les vols commerciaux de l'aviation certifiée.\nL'ULM, le pilote et l'exploitant ne sont pas soumis à des opérations de contrôle préalables de la part de l'autorité. 	2025-04-19 00:00:00
-- 50	saing	elisabeth	elisabethsaing@gmail.com	0675579578	2025-04-19 00:00:00	t	Je reconnais avoir pris connaissance du fait que les exigences applicables aux vols en ULM ne garantissent pas un niveau de sécurité aussi élevé que les vols commerciaux de l'aviation certifiée.\nL'ULM, le pilote et l'exploitant ne sont pas soumis à des opérations de contrôle préalables de la part de l'autorité. 	2025-04-19 00:00:00
-- 51	Jan	Andre	020800@seznam.cz	0692619204	2025-04-19 00:00:00	t	Je reconnais avoir pris connaissance du fait que les exigences applicables aux vols en ULM ne garantissent pas un niveau de sécurité aussi élevé que les vols commerciaux de l'aviation certifiée.\nL'ULM, le pilote et l'exploitant ne sont pas soumis à des opérations de contrôle préalables de la part de l'autorité. 	2025-04-19 00:00:00
-- 52	ROULPH 	Olivier 	olivierkm@hotmail.com	0692440824	2025-04-19 00:00:00	t	Je reconnais avoir pris connaissance du fait que les exigences applicables aux vols en ULM ne garantissent pas un niveau de sécurité aussi élevé que les vols commerciaux de l'aviation certifiée.\nL'ULM, le pilote et l'exploitant ne sont pas soumis à des opérations de contrôle préalables de la part de l'autorité. 	2025-04-19 00:00:00
-- 53	barbier 	charly	charly.barbier@outlook.fr	0613915141	2025-04-20 00:00:00	t	Je reconnais avoir pris connaissance du fait que les exigences applicables aux vols en ULM ne garantissent pas un niveau de sécurité aussi élevé que les vols commerciaux de l'aviation certifiée.\nL'ULM, le pilote et l'exploitant ne sont pas soumis à des opérations de contrôle préalables de la part de l'autorité. 	2025-04-20 00:00:00
-- 54	Roulph	Philippe	proulph@gmail.com	0692221212	2025-04-20 00:00:00	t	Je reconnais avoir pris connaissance du fait que les exigences applicables aux vols en ULM ne garantissent pas un niveau de sécurité aussi élevé que les vols commerciaux de l'aviation certifiée.\nL'ULM, le pilote et l'exploitant ne sont pas soumis à des opérations de contrôle préalables de la part de l'autorité. 	2025-04-20 00:00:00
-- 55	Descamps	Thymoté	thymote.descamps@gmail.com	0772271352	2025-04-20 00:00:00	t	Je reconnais avoir pris connaissance du fait que les exigences applicables aux vols en ULM ne garantissent pas un niveau de sécurité aussi élevé que les vols commerciaux de l'aviation certifiée.\nL'ULM, le pilote et l'exploitant ne sont pas soumis à des opérations de contrôle préalables de la part de l'autorité. 	2025-04-20 00:00:00
-- 56	Maillot	Sébastien	sebastien.maillot@gmx.fr	0692406298	2025-09-05 00:00:00	t	Je reconnais avoir pris connaissance du fait que les exigences applicables aux vols en ULM ne garantissent pas un niveau de sécurité aussi élevé que les vols commerciaux de l'aviation certifiée.\nL'ULM, le pilote et l'exploitant ne sont pas soumis à des opérations de contrôle préalables de la part de l'autorité. 	2025-09-05 00:00:00
-- 57	Maillot	Sébastien	sebastien.maillot@gmx.fr	0692406298	2025-09-05 00:00:00	t	Je reconnais avoir pris connaissance du fait que les exigences applicables aux vols en ULM ne garantissent pas un niveau de sécurité aussi élevé que les vols commerciaux de l'aviation certifiée.\nL'ULM, le pilote et l'exploitant ne sont pas soumis à des opérations de contrôle préalables de la part de l'autorité. 	2025-09-05 00:00:00
-- 58	Maillot	Sébastien	sebastien.maillot@gmx.fr	0692406298	2025-09-05 00:00:00	f	Je reconnais avoir pris connaissance du fait que les exigences applicables aux vols en ULM ne garantissent pas un niveau de sécurité aussi élevé que les vols commerciaux de l'aviation certifiée.\nL'ULM, le pilote et l'exploitant ne sont pas soumis à des opérations de contrôle préalables de la part de l'autorité. 	2025-09-05 00:00:00
-- 59	Maillot	Sébastien	sebastien.maillot@gmx.fr	0692406298	2025-09-05 00:00:00	t	Je reconnais avoir pris connaissance du fait que les exigences applicables aux vols en ULM ne garantissent pas un niveau de sécurité aussi élevé que les vols commerciaux de l'aviation certifiée.\nL'ULM, le pilote et l'exploitant ne sont pas soumis à des opérations de contrôle préalables de la part de l'autorité.	2025-09-05 00:00:00
-- 61	Maillot	Sébastien	sebastien.maillot@gmx.fr	0692406298	2025-09-05 00:00:00	f	Je reconnais avoir pris connaissance du fait que les exigences applicables aux vols en ULM ne garantissent pas un niveau de sécurité aussi élevé que les vols commerciaux de l'aviation certifiée.\nL'ULM, le pilote et l'exploitant ne sont pas soumis à des opérations de contrôle préalables de la part de l'autorité. 	2025-09-05 13:28:30
-- 62	Germain	Freddy	m_seb@icloud.com	0692406298	2025-09-05 00:00:00	f	Je reconnais avoir pris connaissance du fait que les exigences applicables aux vols en ULM ne garantissent pas un niveau de sécurité aussi élevé que les vols commerciaux de l'aviation certifiée.\nL'ULM, le pilote et l'exploitant ne sont pas soumis à des opérations de contrôle préalables de la part de l'autorité. 	2025-09-05 13:30:08
-- 65	Bruno	Dijoux	m_seb@icloud.com	0692406298	2025-09-05 00:00:00	t	Je reconnais avoir pris connaissance du fait que les exigences applicables aux vols en ULM ne garantissent pas un niveau de sécurité aussi élevé que les vols commerciaux de l'aviation certifiée.\nL'ULM, le pilote et l'exploitant ne sont pas soumis à des opérations de contrôle préalables de la part de l'autorité. 	2025-09-05 13:33:31
-- \.


--
-- Data for Name: payment; Type: TABLE DATA; Schema: public; Owner: app
--

COPY public.payment (id, reference, name, date, label, reservation_code, remarques) FROM stdin;
3	PAY-1749178453165-JSVYWZ		2025-06-06	Jed : Paiement des instructions du mois de Mars		\N
4	PAY-1749192024925-4IYP9H	Lecordier	2025-04-04		/reservations/108	\N
2	PAY-1749138165569-TQVEAD	COEURDEVEY Amélie	2025-04-09		/reservations/32	\N
5	PAY-1749310940683-0NE58U	Solon	2025-04-18		/reservations/146	\N
6	PAY-1749311338327-18GPWN	Levacher	2025-04-16		/reservations/140	\N
7	PAY-1749311966476-GVEUJL	rault	2025-04-16		/reservations/126	\N
8	PAY-1749313397487-PJGWA2	Malodobry	2025-04-16		/reservations/142	\N
9	PAY-1749313481406-FHQU7D	Malodobry	2025-04-16		/reservations/142	\N
10	PAY-1749313603201-T0MXF4	Malodobry	2025-04-16		/reservations/142	\N
11	PAY-1749315083282-D5PVTC	rault	2025-04-16		/reservations/126	\N
12	PAY-1749315352801-RGZ3FN	Guimier	2025-04-18		/reservations/149	\N
13	PAY-1749315551420-0DR6XR	Moulin	2025-04-22	\N	/reservations/165	\N
14	PAY-1749315672030-67VT50	Boris	2025-04-22	\N	/reservations/162	\N
15	PAY-1749315750884-BHBS2W	Galibert	2025-04-23	\N	\N	\N
16	PAY-1749315822220-4CD50G	Maistret	2025-04-23	\N	\N	\N
17	PAY-1749349090914-WAEMNC	Hugues	2025-04-20	\N	\N	\N
18	PAY-1749349177143-HIKDV3	Hugues	2025-04-20	\N	\N	\N
20	PAY-1749372760350-EGAWTH	rault	2025-04-16	\N	\N	\N
21	PAY-1749711398705-WNXM8K	PAYET Rachel	2025-06-12	\N	\N	\N
22	PAY-1749711925783-F9XC3A	\N	2025-06-12	Francky : Instruction du 12/06/2025	\N	\N
23	PAY-1751015874188-BQ5WB3	MOREL Brice	2025-06-29	\N	\N	\N
24	PAY-1753901205888-J21X8G	Johnny HALLIDAY	2025-07-30	\N	RESA-1753807546261-VSDWGK	\N
25	PAY-1753903575286-5Y9UGM	Sébastien Maillot	2025-07-30	\N	RESA-1753768453660-RWDN3T	\N
26	PAY-1754574886413-T2VP3R	Alphonse BROWN	2025-07-31	\N	RESA-1753948350026-2LNJX5	\N
29	PAY-1757862988303-ZPSBBP	Louise BOUILLOUX	2025-09-13	\N	RESA-1757784856346-23VV1Q	\N
28	PAY-1757862819250-OCV5QZ	Ludovic BOYER	2025-09-13	\N	RESA-1757784978513-BD1A25	\N
30	PAY-1757863237013-B7LL75	Sébastien Maillot	2025-09-13	\N	RESA-1757784780995-Q8IHAN	\N
27	PAY-1754576938345-7XJ25Q	Beneficiaire	2025-08-08	\N	RESA-1754576879331-0VMK0K	\N
32	PAY-1757881164966-M7L734	Esteban OCON	2025-09-14	\N	RESA-1757881112091-JSZQG7	\N
33	PAY-1757883152504-515VPC	Thierry HENRI	2025-09-15	\N	RESA-1757875138674-4O3Z8F	\N
31	PAY-1757880855845-1WNWES	Pierre GASLY	2025-09-14	\N	RESA-1757875312567-77935M	\N
\.


--
-- Data for Name: payment_detail; Type: TABLE DATA; Schema: public; Owner: app
--

COPY public.payment_detail (id, mode, amount, payment_id, prepayment_id, expense_id) FROM stdin;
2	cb	210	2	\N	\N
3	cheque	420	3	\N	\N
4	cb	140	4	\N	\N
5	espece	200	\N	\N	\N
6	espece	200	\N	\N	\N
7	especes	200	4	\N	\N
8	web	170	4	\N	\N
9	virement	170	4	\N	\N
10	cb	210	2	\N	\N
11	cb	340	5	\N	\N
12	cb	340	6	\N	\N
13	cb	260	7	\N	\N
14	cb	340	8	\N	\N
15	cb	340	9	\N	\N
16	cb	340	10	\N	\N
17	cb	260	11	\N	\N
18	especes	340	12	\N	\N
19	especes	420	13	\N	\N
20	cb	110	14	\N	\N
21	especes	420	15	\N	\N
22	especes	340	16	\N	\N
23	especes	110	17	\N	\N
24	especes	110	18	\N	\N
26	cb	340	20	\N	\N
27	cb	170	21	\N	\N
28	cheque	135	22	\N	\N
29	cb	210	23	\N	\N
30	especes	210	23	\N	\N
31	cb	170	24	\N	\N
32	web	587	25	7	\N
33	cb	210	26	\N	\N
34	especes	20	\N	\N	\N
35	especes	20	\N	\N	\N
36	web	153	27	8	\N
37	especes	20	\N	\N	\N
39	especes	20	\N	\N	\N
38	especes	20	\N	\N	\N
40	cb	420	\N	\N	1
41	especes	46	\N	\N	1
42	virement	1944	\N	\N	2
44	cb	153	29	\N	\N
45	especes	110	28	\N	\N
43	cb	100	28	\N	\N
47	especes	100	30	\N	\N
46	web	89	30	\N	\N
48	web	189	31	25	\N
49	web	326	32	26	\N
50	web	209	33	23	\N
\.


--
-- Data for Name: payment_origine; Type: TABLE DATA; Schema: public; Owner: app
--

COPY public.payment_origine (payment_id, origine_id) FROM stdin;
28	6
29	5
29	9
29	4
30	5
30	9
31	7
31	8
27	9
32	7
32	9
33	9
33	7
\.


--
-- Data for Name: pilot_qualification; Type: TABLE DATA; Schema: public; Owner: app
--

COPY public.pilot_qualification (id, date_obtention, valid_until, profil_id, qualification_id, is_alert_sent, created_at, updated_at, created_by_id, updated_by_id, document_id) FROM stdin;
8	2025-08-18 00:00:00	\N	12	1	\N	\N	\N	\N	\N	\N
9	2025-08-18 00:00:00	\N	13	7	\N	\N	\N	\N	\N	\N
10	2025-08-18 00:00:00	\N	14	7	\N	\N	\N	\N	\N	\N
11	2025-08-18 00:00:00	\N	15	6	\N	\N	\N	\N	\N	\N
13	2025-08-18 00:00:00	\N	17	7	\N	\N	\N	\N	\N	\N
14	2025-08-18 00:00:00	\N	18	6	\N	\N	\N	\N	\N	\N
37	2025-01-01 00:00:00	\N	43	7	f	\N	2025-09-10 16:57:33	\N	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4	62
33	2025-01-01 00:00:00	\N	38	2	f	2025-09-10 05:56:47	2025-09-11 12:01:20	\N	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4	52
35	2025-01-01 00:00:00	\N	40	1	f	2025-09-10 06:36:46	2025-09-11 12:01:29	\N	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4	57
17	2024-01-04 00:00:00	\N	23	6	\N	\N	\N	\N	\N	\N
36	2025-01-01 00:00:00	\N	41	2	f	2025-09-10 14:26:33	2025-09-14 13:14:00	\N	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4	\N
38	2025-01-01 00:00:00	\N	11	1	\N	\N	\N	\N	\N	\N
39	2025-01-01 00:00:00	\N	16	1	\N	\N	\N	\N	\N	\N
40	2025-01-01 00:00:00	\N	8	1	\N	\N	\N	\N	\N	\N
41	2025-01-01 00:00:00	\N	10	1	\N	\N	\N	\N	\N	\N
42	2025-01-01 00:00:00	\N	10	3	\N	\N	\N	\N	\N	\N
43	2025-01-01 00:00:00	\N	12	1	\N	\N	\N	\N	\N	\N
44	2025-01-01 00:00:00	\N	13	7	\N	\N	\N	\N	\N	\N
45	2025-01-01 00:00:00	\N	14	7	\N	\N	\N	\N	\N	\N
46	2025-01-01 00:00:00	\N	17	7	\N	\N	\N	\N	\N	\N
47	2025-01-01 00:00:00	\N	9	2	\N	\N	\N	\N	\N	\N
48	2025-01-01 00:00:00	\N	9	3	\N	\N	\N	\N	\N	\N
49	2025-01-01 00:00:00	\N	9	4	\N	\N	\N	\N	\N	\N
50	2025-01-01 00:00:00	\N	15	6	\N	\N	\N	\N	\N	\N
51	2025-01-01 00:00:00	\N	18	6	\N	\N	\N	\N	\N	\N
2	2025-05-01 00:00:00	\N	9	2	\N	\N	\N	\N	\N	\N
3	2025-01-01 00:00:00	\N	9	3	\N	\N	\N	\N	\N	\N
4	2025-01-01 00:00:00	\N	9	4	\N	\N	\N	\N	\N	\N
7	2025-08-18 00:00:00	\N	11	1	f	\N	2025-09-03 18:14:07	\N	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4	\N
18	2025-08-11 00:00:00	\N	11	3	f	\N	2025-09-03 18:14:07	\N	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4	\N
21	2024-07-01 00:00:00	\N	26	1	f	\N	2025-09-03 18:14:13	\N	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4	\N
5	2025-08-11 00:00:00	\N	10	1	\N	\N	\N	\N	\N	\N
19	2025-08-18 00:00:00	\N	10	2	\N	\N	\N	\N	\N	\N
6	2025-08-25 00:00:00	\N	10	3	\N	\N	\N	\N	\N	\N
22	2018-01-12 00:00:00	\N	27	1	f	\N	2025-09-03 18:14:19	\N	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4	\N
29	2025-08-31 00:00:00	\N	34	1	f	2025-08-31 12:23:02	2025-09-03 18:14:35	\N	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4	5
12	2025-08-18 00:00:00	\N	16	1	f	\N	2025-09-03 18:50:18	\N	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4	\N
1	2025-08-11 00:00:00	\N	8	1	f	\N	2025-09-05 15:34:40	\N	1f01ebb8-f485-6bce-81d0-afc6e2cc12c4	43
\.


--
-- Data for Name: profil_pilote_qualification; Type: TABLE DATA; Schema: public; Owner: app
--

COPY public.profil_pilote_qualification (profil_pilote_id, qualification_id) FROM stdin;
9	3
9	2
10	1
10	3
11	1
12	1
16	1
8	1
9	4
13	7
14	7
17	7
15	6
18	6
\.


--
-- Data for Name: rappel; Type: TABLE DATA; Schema: public; Owner: app
--

COPY public.rappel (id, date, titre, description, recurrent, jour, important, finished) FROM stdin;
4	2025-04-24	Test	Encore un test	f	4	t	f
9	2025-07-02	Test du retour sur le calendrier	La redirection ramène-t-elle sur la bonne date ?	f	3	f	f
10	2025-07-04	Test de la redirection	On test le comportement de la redirection depuis le retrait de force refresh	f	5	t	f
\.


--
-- Data for Name: reservation; Type: TABLE DATA; Schema: public; Owner: app
--

COPY public.reservation (id, nom, telephone, quantite, prix, debut, fin, color, statut, remarques, circuit_id, option_id, pilote_id, avion_id, report, email, "position", paid, upsell, cadeau_id, code, payment_reference) FROM stdin;
30	RAETSCHELDER Ingrid	+32476889421	2	210	2025-04-05 02:45:00	2025-04-05 04:00:00	#23098b	VALIDATED		1	\N	1f000a03-4784-69e0-85cf-c39afce9e08d	1	f		\N	\N	\N	\N	\N	\N
115	Francky		1	135	2025-04-05 04:30:00	2025-04-05 05:30:00	#9797d7	VALIDATED		5	\N	1f000a01-0394-65de-b10d-c39afce9e08d	3	f		\N	\N	\N	\N	\N	\N
14	HENRION Clémence	+32499377110	2	170	2025-04-07 04:00:00	2025-04-07 05:00:00	#a95c24	VALIDATED		2	\N	1f000a03-4784-69e0-85cf-c39afce9e08d	1	t		\N	\N	\N	\N	\N	\N
32	COEURDEVEY Amélie	+33633302342	2	170	2025-04-09 04:15:00	2025-04-09 05:15:00	#53b3fb	VALIDATED		2	\N	1f000a03-4784-69e0-85cf-c39afce9e08d	4	t	amelie.coeurdevey@outlook.fr	\N	\N	\N	\N	\N	\N
33	COEURDEVEY Amélie	+33633302342	2	170	2025-04-09 04:15:00	2025-04-09 05:15:00	#53b3fb	VALIDATED		2	\N	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	1	t	amelie.coeurdevey@outlook.fr	\N	\N	\N	\N	\N	\N
34	LARCHER Marjorie	+33671662951	2	210	2025-04-11 02:45:00	2025-04-11 04:00:00	#1e952b	VALIDATED		1	\N	1f000a03-4784-69e0-85cf-c39afce9e08d	4	f		\N	\N	\N	\N	\N	\N
35	LARCHER Marjorie	+33671662951	2	210	2025-04-11 02:45:00	2025-04-11 04:00:00	#1e952b	VALIDATED		1	\N	1f000a05-b11b-699c-8cc6-c39afce9e08d	1	f		\N	\N	\N	\N	\N	\N
36	LEBRUN	+33677134673	3	210	2025-04-11 04:15:00	2025-04-11 05:30:00	#5da83e	VALIDATED		1	\N	1f000a03-4784-69e0-85cf-c39afce9e08d	4	t		\N	\N	\N	\N	\N	\N
37	LEBRUN	+33677134673	3	210	2025-04-11 04:15:00	2025-04-11 05:30:00	#5da83e	VALIDATED		1	\N	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	3	t		\N	\N	\N	\N	\N	\N
38	LEBRUN	+33677134673	3	210	2025-04-11 04:15:00	2025-04-11 05:30:00	#5da83e	VALIDATED		1	\N	1f000a05-b11b-699c-8cc6-c39afce9e08d	1	t		\N	\N	\N	\N	\N	\N
42	LE HIR	+33658162464	2	170	2025-04-11 06:00:00	2025-04-11 07:00:00	#24c1a	VALIDATED		2	\N	1f000a05-b11b-699c-8cc6-c39afce9e08d	1	f		\N	\N	\N	\N	\N	\N
43	CHAPOT	0693927688	2	210	2025-04-14 02:45:00	2025-04-14 04:00:00	#6a5b00	VALIDATED	Prépaiement de 230€ - COURTADE du 26/01/25.	1	\N	1f000a01-0394-65de-b10d-c39afce9e08d	1	f		\N	\N	\N	\N	\N	\N
39	GUTIERREZ	+33642501785	2	210	2025-04-11 05:45:00	2025-04-11 07:00:00	#d1b8ba	VALIDATED	PLANETAIR ALPHA	1	\N	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	2	f		\N	\N	\N	\N	\N	\N
41	LE HIR	+33658162464	2	170	2025-04-11 06:00:00	2025-04-11 07:00:00	#24c1a	VALIDATED		2	\N	1f000a01-0394-65de-b10d-c39afce9e08d	3	t		\N	\N	\N	\N	\N	\N
67	DUPUIS		4	170	2025-04-15 04:00:00	2025-04-15 05:00:00	#284281	VALIDATED		2	\N	1f000a05-b11b-699c-8cc6-c39afce9e08d	2	t		\N	\N	\N	\N	\N	\N
16	JED		1	135	2025-03-23 04:00:00	2025-03-23 05:00:00	#a4c2cc	VALIDATED	Vol solo.	5	\N	\N	\N	f		\N	\N	\N	\N	\N	\N
64	DUPUIS	0635974151	4	170	2025-04-15 02:45:00	2025-04-15 03:45:00	#284281	VALIDATED		2	\N	1f000a03-4784-69e0-85cf-c39afce9e08d	1	f		\N	\N	\N	\N	\N	\N
65	DUPUIS		4	170	2025-04-15 02:45:00	2025-04-15 03:45:00	#284281	VALIDATED		2	\N	1f000a05-b11b-699c-8cc6-c39afce9e08d	2	f		\N	\N	\N	\N	\N	\N
66	DUPUIS		4	170	2025-04-15 04:00:00	2025-04-15 05:00:00	#284281	VALIDATED		2	\N	1f000a03-4784-69e0-85cf-c39afce9e08d	1	t		\N	\N	\N	\N	\N	\N
23	PARANT Gérard	+22612127385	2	170	2025-03-29 03:15:00	2025-03-29 04:15:00	#fac08e	VALIDATED		2	\N	\N	\N	f		\N	\N	\N	\N	\N	\N
24	PARANT Gérard	+22612127385	2	170	2025-03-29 03:15:00	2025-03-29 04:15:00	#fac08e	VALIDATED		2	2	\N	\N	f		\N	\N	\N	\N	\N	\N
25	MOUGEL	+33686012333	2	210	2025-03-30 02:15:00	2025-03-30 03:30:00	#8a97a8	VALIDATED		1	\N	\N	\N	f		\N	\N	\N	\N	\N	\N
26	MOUGEL	+33686012333	2	210	2025-03-30 02:15:00	2025-03-30 03:30:00	#8a97a8	VALIDATED		1	\N	\N	\N	f		\N	\N	\N	\N	\N	\N
27	DARCEY PIERRE	+33664737228	1	210	2025-03-30 02:15:00	2025-03-30 03:30:00	#39483b	VALIDATED	Prépaiement au nom de DARCEY Pierre, du 27/12/24.	1	\N	\N	\N	f		\N	\N	\N	\N	\N	\N
45	REKESAN Claudia	+41796856268	1	190	2025-04-25 03:00:00	2025-04-25 04:00:00	#5321d8	VALIDATED		2	1	\N	\N	f		\N	\N	\N	\N	\N	\N
46	REKESAN David	+41796856268	1	230	2025-04-25 03:00:00	2025-04-25 04:15:00	#b35848	VALIDATED		1	1	\N	\N	f		\N	\N	\N	\N	\N	\N
47	VANDERBEKE	+33760967795	2	170	2025-04-25 05:45:00	2025-04-25 06:45:00	#946928	VALIDATED		2	\N	\N	\N	t		\N	\N	\N	\N	\N	\N
49	TEXIER	+33640250153	2	210	2025-04-28 04:00:00	2025-04-28 05:15:00	#124efb	VALIDATED		1	\N	\N	\N	f		\N	\N	\N	\N	\N	\N
50	TEXIER	+33640250153	2	210	2025-04-28 04:00:00	2025-04-28 05:15:00	#124efb	VALIDATED		1	\N	\N	\N	f		\N	\N	\N	\N	\N	\N
51	CONTI	+33672914604	3	170	2025-04-30 02:45:00	2025-04-30 03:45:00	#1c8cb1	VALIDATED		2	\N	\N	\N	f		\N	\N	\N	\N	\N	\N
52	CONTI	+33672914604	3	170	2025-04-30 02:45:00	2025-04-30 03:45:00	#1c8cb1	VALIDATED		2	\N	\N	\N	f		\N	\N	\N	\N	\N	\N
53	CONTI	+33672914604	3	170	2025-04-30 02:45:00	2025-04-30 03:45:00	#1c8cb1	VALIDATED		2	\N	\N	\N	f		\N	\N	\N	\N	\N	\N
56	NICOLAS 	+33623665780	2	170	2025-03-28 04:00:00	2025-03-28 05:00:00	#1dbe7f	VALIDATED		2	\N	\N	\N	f		\N	\N	\N	\N	\N	\N
13	METEYER Mathilde	0699265252	1	210	2025-03-26 02:45:00	2025-03-26 04:00:00	#dd742f	VALIDATED		1	\N	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	1	f		\N	\N	\N	\N	\N	\N
55	NICOLAS 	+33623575780	2	170	2025-03-28 04:00:00	2025-03-28 05:00:00	#1dbe7f	VALIDATED		2	\N	\N	\N	f		\N	\N	\N	\N	\N	\N
10	CANEL	0769335740	2	210	2025-03-21 04:00:00	2025-03-21 05:15:00	#95e4e8	VALIDATED	Prépaiement de 140€. Reste à régler 280€.	1	\N	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	3	f		\N	\N	\N	\N	\N	\N
11	CANEL	0769335740	2	210	2025-03-21 04:00:00	2025-03-21 05:15:00	#95e4e8	VALIDATED	Prépaiement de 140€. Reste à régler 280€.	1	\N	1f000a05-b11b-699c-8cc6-c39afce9e08d	2	t		\N	\N	\N	\N	\N	\N
63	FEZAS	+33640641742	1	170	2025-03-31 04:00:00	2025-03-31 05:00:00	#8e710d	VALIDATED		2	\N	\N	\N	f		\N	\N	\N	\N	\N	\N
62	JED		1	135	2025-03-30 04:00:00	2025-03-30 05:00:00	#a2374e	VALIDATED		5	\N	\N	\N	f		\N	\N	\N	\N	\N	\N
59	NICOLAY	+33786433538	2	210	2025-03-24 02:45:00	2025-03-24 04:00:00	#131557	VALIDATED		1	\N	1f000a03-4784-69e0-85cf-c39afce9e08d	2	f		\N	\N	\N	\N	\N	\N
22	ROBERT JOE	0693035442	1	150	2025-03-27 02:45:00	2025-03-27 03:30:00	#d598a5	VALIDATED		4	1	\N	\N	t		\N	\N	\N	\N	\N	\N
60	NICOLAY	+33786433538	2	210	2025-03-24 02:45:00	2025-03-24 04:00:00	#131557	VALIDATED		1	\N	1f000a05-b11b-699c-8cc6-c39afce9e08d	3	f		\N	\N	\N	\N	\N	\N
57	BONJOUR Elisabeth	+33662585904	2	170	2025-03-24 04:15:00	2025-03-24 05:15:00	#156415	VALIDATED		2	\N	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	4	f		\N	\N	\N	\N	\N	\N
12	PLANCKE Florence		1	130	2025-05-17 05:00:00	2025-05-17 05:45:00	#c795a9	VALIDATED	Prépayé (Boittin du 13/12/24)	4	\N	\N	\N	f	florence.plancke@gmail.com	\N	\N	\N	\N	\N	\N
58	BONJOUR Elisabeth	+33662585904	2	170	2025-03-24 04:15:00	2025-03-24 05:15:00	#156415	VALIDATED		2	2	1f000a05-b11b-699c-8cc6-c39afce9e08d	3	f		\N	\N	\N	\N	\N	\N
17	DAYDE Armand	0692619919	1	180	2025-03-24 04:15:00	2025-03-24 05:15:00	#7c7329	VALIDATED		8	\N	1f000a03-4784-69e0-85cf-c39afce9e08d	2	f		\N	\N	\N	\N	\N	\N
54	Ravoux	0692314412	1	140	2025-03-25 02:45:00	2025-03-25 03:35:00	#333299	VALIDATED	contact boris	3	\N	1f000a05-b11b-699c-8cc6-c39afce9e08d	3	f		\N	\N	\N	\N	\N	\N
21	POUPIN	+33785531101	2	210	2025-03-26 04:00:00	2025-03-26 05:15:00	#349c2e	VALIDATED	Prépayé	1	\N	1f000a03-4784-69e0-85cf-c39afce9e08d	3	f		\N	\N	\N	\N	\N	\N
20	POUPIN	+33785531101	2	210	2025-03-26 04:00:00	2025-03-26 05:15:00	#349c2e	VALIDATED	Prépayé	1	\N	1f00971f-f3f5-6d56-b729-1315ef24fbc8	4	f		\N	\N	\N	\N	\N	\N
18	DUBREUIL	+33648839507	2	140	2025-03-26 02:45:00	2025-03-26 03:35:00	#9f2120	VALIDATED	Prépayé	3	\N	1f000a03-4784-69e0-85cf-c39afce9e08d	3	f		\N	\N	\N	\N	\N	\N
28	MASSON Pascale	+33664757592	2	210	2025-04-01 03:00:00	2025-04-01 04:15:00	#1ac1bb	VALIDATED		1	\N	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	3	f		\N	\N	\N	\N	\N	\N
29	MASSON Pascale	+33664757592	2	210	2025-04-01 03:00:00	2025-04-01 04:15:00	#1ac1bb	VALIDATED		1	\N	1f000a05-b11b-699c-8cc6-c39afce9e08d	2	t		\N	\N	\N	\N	\N	\N
70	MILLOCHAU	+33767609715	2	170	2025-05-04 02:45:00	2025-05-04 03:45:00	#572fb7	VALIDATED		2	\N	\N	\N	f		\N	\N	\N	\N	\N	\N
71	MILLOCHAU	+33767609715	2	170	2025-05-04 02:45:00	2025-05-04 03:45:00	#572fb7	VALIDATED		2	\N	\N	\N	f		\N	\N	\N	\N	\N	\N
72	BEUGNET		3	170	2025-05-04 04:00:00	2025-05-04 05:00:00	#74d900	VALIDATED		2	\N	\N	\N	f		\N	\N	\N	\N	\N	\N
73	BEUGNET		3	170	2025-05-04 04:00:00	2025-05-04 05:00:00	#74d900	VALIDATED		2	\N	\N	\N	f		\N	\N	\N	\N	\N	\N
74	BEUGNET		3	170	2025-05-04 04:00:00	2025-05-04 05:00:00	#74d900	VALIDATED		2	\N	\N	\N	f		\N	\N	\N	\N	\N	\N
75	DEGLICOURT	+33693636815	2	130	2025-05-05 04:00:00	2025-05-05 04:45:00	#7b504f	VALIDATED		4	\N	\N	\N	f		\N	\N	\N	\N	\N	\N
76	DEGLICOURT	+33693636815	2	130	2025-05-05 04:00:00	2025-05-05 04:45:00	#7b504f	VALIDATED		4	\N	\N	\N	f		\N	\N	\N	\N	\N	\N
77	GAUTHIER Anaïs	0692619204	1	170	2025-05-06 03:00:00	2025-05-06 04:00:00	#aea7dd	VALIDATED		2	\N	\N	\N	f		\N	\N	\N	\N	\N	\N
78	LAFAYE	+33675656914	2	210	2025-05-06 04:00:00	2025-05-06 05:15:00	#ae857d	VALIDATED		1	\N	\N	\N	f		\N	\N	\N	\N	\N	\N
79	LAFAYE	+33675656914	2	210	2025-05-06 04:00:00	2025-05-06 05:15:00	#ae857d	VALIDATED		1	\N	\N	\N	f		\N	\N	\N	\N	\N	\N
80	STEMBERG MILAN	+420774489509	2	170	2025-05-07 04:00:00	2025-05-07 05:00:00	#584985	VALIDATED		2	\N	\N	\N	f		\N	\N	\N	\N	\N	\N
81	STEMBERG MILAN	+420774489509	2	170	2025-05-07 04:00:00	2025-05-07 05:00:00	#584985	VALIDATED		2	\N	\N	\N	f		\N	\N	\N	\N	\N	\N
82	PETIT	+33652925490	2	210	2025-05-11 04:00:00	2025-05-11 05:15:00	#9f4278	VALIDATED		1	\N	\N	\N	f		\N	\N	\N	\N	\N	\N
83	PETIT	+33652925490	2	210	2025-05-11 04:00:00	2025-05-11 05:15:00	#9f4278	VALIDATED		1	\N	\N	\N	f		\N	\N	\N	\N	\N	\N
84	CHENTIR	+33662609344	1	135	2025-05-11 05:30:00	2025-05-11 06:30:00	#e25d71	VALIDATED		5	\N	\N	\N	f		\N	\N	\N	\N	\N	\N
85	BRUGGER		2	140	2025-05-19 02:45:00	2025-05-19 03:35:00	#c7b425	VALIDATED		3	\N	\N	\N	f		\N	\N	\N	\N	\N	\N
86	BRUGGER		2	140	2025-05-19 02:45:00	2025-05-19 03:35:00	#c7b425	VALIDATED		3	\N	\N	\N	f		\N	\N	\N	\N	\N	\N
87	ANQUETIL Virginie	+33769806737	2	210	2025-05-19 04:00:00	2025-05-19 05:15:00	#ce1e65	VALIDATED		1	\N	\N	\N	f		\N	\N	\N	\N	\N	\N
88	ANQUETIL Virginie	+33769806737	2	210	2025-05-19 04:00:00	2025-05-19 05:15:00	#ce1e65	VALIDATED		1	\N	\N	\N	f		\N	\N	\N	\N	\N	\N
89	AZEVEDO	+33684935920	2	170	2025-06-02 04:00:00	2025-06-02 05:00:00	#6bd2db	VALIDATED		2	\N	\N	\N	f		\N	\N	\N	\N	\N	\N
90	AZEVEDO	+33684935920	2	170	2025-06-02 04:00:00	2025-06-02 05:00:00	#6bd2db	VALIDATED		2	\N	\N	\N	f		\N	\N	\N	\N	\N	\N
91	ROUGE Florence	+33682495739	2	170	2025-06-04 04:00:00	2025-06-04 05:00:00	#1ff466	VALIDATED		2	\N	\N	\N	f		\N	\N	\N	\N	\N	\N
92	ROUGE Florence	+33682495739	2	170	2025-06-04 04:00:00	2025-06-04 05:00:00	#1ff466	VALIDATED		2	\N	\N	\N	f		\N	\N	\N	\N	\N	\N
93	MIRKOVIK	+33601197479	2	210	2025-06-28 04:00:00	2025-06-28 05:15:00	#f6a90b	VALIDATED		1	\N	\N	\N	f		\N	\N	\N	\N	\N	\N
94	MIRKOVIK	+33601197479	2	210	2025-06-28 04:00:00	2025-06-28 05:15:00	#f6a90b	VALIDATED		1	\N	\N	\N	f		\N	\N	\N	\N	\N	\N
103	JED		1	135	2025-04-01 04:30:00	2025-04-01 05:30:00	#3524ee	VALIDATED		5	\N	\N	1	t		\N	\N	\N	\N	\N	\N
61	SCHMITT Lisa	+33650230940	1	170	2025-03-25 04:00:00	2025-03-25 05:00:00	#d01fd4	VALIDATED		2	\N	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	4	f		\N	\N	\N	\N	\N	\N
96	Robertas Pogorelis	+32455131887	2	170	2025-03-28 05:15:00	2025-03-28 06:15:00	#98ce6e	VALIDATED		2	\N	1f000a03-4784-69e0-85cf-c39afce9e08d	1	f	Rpogorelis@yahoo.com	\N	\N	\N	\N	\N	\N
97	Robertas Pogorelis	+32455131887	2	170	2025-03-28 05:15:00	2025-03-28 06:15:00	#98ce6e	VALIDATED		2	\N	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	4	f	Rpogorelis@yahoo.com	\N	\N	\N	\N	\N	\N
116	Roolphie		1	135	2025-04-05 04:00:00	2025-04-05 05:00:00	#5cc25	VALIDATED		5	\N	1f000a03-4784-69e0-85cf-c39afce9e08d	1	f		\N	\N	\N	\N	\N	\N
98	Dubreuil	0648839507	1	140	2025-03-26 02:45:00	2025-03-26 03:35:00	#74141a	VALIDATED		3	\N	1f00971f-f3f5-6d56-b729-1315ef24fbc8	4	f		\N	\N	\N	\N	\N	\N
99	Loustic		1	0	2025-03-26 04:15:00	2025-03-26 05:15:00	#96d977	VALIDATED		7	\N	\N	\N	f		\N	\N	\N	\N	\N	\N
100	SUBRA Jeanne	+33769680664	2	210	2025-03-28 02:45:00	2025-03-28 04:00:00	#789a82	VALIDATED	Wein Location - 10% de remise	1	\N	\N	\N	f		\N	\N	\N	\N	\N	\N
101	SUBRA Jeanne	+33769680664	2	210	2025-03-28 02:45:00	2025-03-28 04:00:00	#789a82	VALIDATED	Wein Location - 10% de remise	1	\N	\N	\N	f		\N	\N	\N	\N	\N	\N
102	FIORE	+33619409505	1	170	2025-03-28 02:45:00	2025-03-28 03:45:00	#839dd8	VALIDATED		2	\N	\N	\N	f		\N	\N	\N	\N	\N	\N
31	RAETSCHELDER Ingrid	+32476889421	2	210	2025-04-05 02:45:00	2025-04-05 04:00:00	#23098b	VALIDATED		1	\N	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	2	f		\N	\N	\N	\N	\N	\N
114	Cochard	0658372328	1	210	2025-04-05 04:00:00	2025-04-05 05:15:00	#d693a4	VALIDATED		1	\N	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	2	f		\N	\N	\N	\N	\N	\N
105	Sazerac angelique	0692425168	1	210	2025-04-01 04:30:00	2025-04-01 05:45:00	#a5a86b	VALIDATED		1	\N	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	3	t		\N	\N	\N	\N	\N	\N
106	Dancerel	0679878975	1	210	2025-04-03 02:45:00	2025-04-03 04:00:00	#e4b6d6	VALIDATED		1	\N	\N	\N	f		\N	\N	\N	\N	\N	\N
107	Tricard	0637637616	1	210	2025-04-03 04:15:00	2025-04-03 05:30:00	#e38c5a	VALIDATED		1	\N	\N	\N	f		\N	\N	\N	\N	\N	\N
117	Tiphaine	0658836986	2	170	2025-04-07 02:45:00	2025-04-07 03:45:00	#6fa194	VALIDATED	Porte	2	\N	1f000a03-4784-69e0-85cf-c39afce9e08d	1	f		\N	\N	\N	\N	\N	\N
118	Tiphaine	0658836986	2	170	2025-04-07 02:45:00	2025-04-07 03:45:00	#6fa194	VALIDATED		2	\N	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	2	f		\N	\N	\N	\N	\N	\N
108	Lecordier	0781028256	1	170	2025-04-04 02:45:00	2025-04-04 03:45:00	#85f5d3	VALIDATED	Prépayé reste à régler 30€. Upsell	2	\N	1f00971f-f3f5-6d56-b729-1315ef24fbc8	1	f		\N	\N	\N	\N	\N	\N
15	HENRION Clémence	+32499377110	2	170	2025-04-07 04:00:00	2025-04-07 05:00:00	#a95c24	VALIDATED		2	\N	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	2	t		\N	\N	\N	\N	\N	\N
109	Ferrand	0767609987	1	170	2025-04-04 04:00:00	2025-04-04 05:00:00	#302e89	VALIDATED		2	\N	1f000a03-4784-69e0-85cf-c39afce9e08d	2	f		\N	\N	\N	\N	\N	\N
110	Ferrand	0767609987	1	170	2025-04-04 04:00:00	2025-04-04 05:00:00	#b46d9	VALIDATED		2	\N	1f00971f-f3f5-6d56-b729-1315ef24fbc8	1	f		\N	\N	\N	\N	\N	\N
111	Valero	0648037356	1	130	2025-04-04 02:45:00	2025-04-04 03:30:00	#67441e	VALIDATED		4	\N	1f000a03-4784-69e0-85cf-c39afce9e08d	2	f		\N	\N	\N	\N	\N	\N
112	Sabine	0693552519	2	210	2025-04-04 05:15:00	2025-04-04 06:30:00	#d21e38	VALIDATED		1	\N	1f000a03-4784-69e0-85cf-c39afce9e08d	2	f		\N	\N	\N	\N	\N	\N
120	Hugues		3	210	2025-04-09 02:45:00	2025-04-09 04:00:00	#c2320	VALIDATED		1	\N	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	1	f		\N	\N	\N	\N	\N	\N
113	Sabine	0693552519	2	210	2025-04-04 05:15:00	2025-04-04 06:30:00	#d21e38	VALIDATED		1	\N	1f00971f-f3f5-6d56-b729-1315ef24fbc8	1	f		\N	\N	\N	\N	\N	\N
119	Hugues		3	210	2025-04-09 02:45:00	2025-04-09 04:00:00	#c2320	VALIDATED		1	\N	1f000a03-4784-69e0-85cf-c39afce9e08d	4	f		\N	\N	\N	\N	\N	\N
124	Nicolas	0677181654	2	210	2025-04-09 05:30:00	2025-04-09 06:45:00	#90afaa	VALIDATED		1	\N	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	1	t		\N	\N	\N	\N	\N	\N
121	Hugues		3	210	2025-04-09 02:45:00	2025-04-09 04:00:00	#c2320	VALIDATED		1	\N	1f000a01-0394-65de-b10d-c39afce9e08d	2	f		\N	\N	\N	\N	\N	\N
123	Nicolas	0677181654	2	210	2025-04-09 05:30:00	2025-04-09 06:45:00	#90afaa	VALIDATED		1	\N	1f000a03-4784-69e0-85cf-c39afce9e08d	4	t		\N	\N	\N	\N	\N	\N
122	Loustic		1	170	2025-04-09 04:15:00	2025-04-09 05:15:00	#e09ec0	VALIDATED		2	\N	1f000a01-0394-65de-b10d-c39afce9e08d	2	t		\N	\N	\N	\N	\N	\N
125	Séb	0692406298	1	110	2025-04-10 03:15:00	2025-04-10 04:15:00	#57859c	VALIDATED		6	\N	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	3	f		\N	\N	\N	\N	\N	\N
40	GUTIERREZ	+33642501785	2	210	2025-04-11 05:45:00	2025-04-11 07:00:00	#d1b8ba	VALIDATED		1	\N	1f000a03-4784-69e0-85cf-c39afce9e08d	4	f		\N	\N	\N	\N	\N	\N
128	williamson		1	0	2025-04-12 06:00:00	2025-04-12 07:00:00	#6896ac	VALIDATED		7	\N	\N	\N	f		\N	\N	\N	\N	\N	\N
351	RETAILLEAU Bruno	0692406298	2	170	2025-09-13 06:30:00	2025-09-13 07:30:00	#7e6818	VALIDATED		2	\N	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	2	f	sebastien.maillot@gmx.fr	Leader	f	f	\N	RESA-1757771115383-Q2JH3W	\N
142	Malodobry	+48662062505	2	170	2025-04-16 05:00:00	2025-04-16 06:00:00	#e2b7ba	VALIDATED		2	\N	1f000a03-4784-69e0-85cf-c39afce9e08d	1	f		Leader	f	f	\N	\N	\N
143	Malodobry	+48662062505	2	170	2025-04-16 05:00:00	2025-04-16 06:00:00	#e2b7ba	VALIDATED		2	\N	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	4	f		2	f	f	\N	\N	\N
140	Levacher	0648142776	2	170	2025-04-16 02:45:00	2025-04-16 03:45:00	#500fdc	VALIDATED		2	\N	1f000a03-4784-69e0-85cf-c39afce9e08d	1	f		Leader	t	f	\N	RESA-1749311480641-KHRWP2	\N
293	Antoine DUPONT	0692406298	2	153	2025-08-01 06:00:00	2025-08-01 07:00:00	#4c59ad	VALIDATED		2	1	\N	\N	f	sebastien.maillot@gmx.fr	\N	t	f	10	RESA-1754061613932-3LT827	\N
135	christinq kupper	+49 1727734376	1	170	2025-04-11 02:45:00	2025-04-11 03:45:00	#e11b75	VALIDATED	contact whatsapp \nsera sur site a 6h30 quoiqu.il arrive si pas contact	2	\N	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	3	f		\N	\N	\N	\N	\N	\N
44	CHAPOT	0693927688	2	230	2025-04-14 02:45:00	2025-04-14 04:00:00	#6a5b00	VALIDATED	Prépaiement de 230€ - COURTADE du 26/01/25.	1	1	1f000a05-b11b-699c-8cc6-c39afce9e08d	2	f		\N	\N	\N	\N	\N	\N
146	Solon	0676865902	3	150	2025-04-18 04:00:00	2025-04-18 04:45:00	#4a3a89	VALIDATED		4	1	1f000a05-b11b-699c-8cc6-c39afce9e08d	1	f		3	f	f	\N	\N	\N
132	Baillet		2	210	2025-04-14 04:15:00	2025-04-14 05:30:00	#a4f13f	VALIDATED		1	\N	1f000a03-4784-69e0-85cf-c39afce9e08d	4	t		\N	\N	\N	\N	\N	\N
133	Baillet		2	210	2025-04-14 04:15:00	2025-04-14 05:30:00	#a4f13f	VALIDATED		1	\N	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	3	t		\N	\N	\N	\N	\N	\N
134	Cuyelle	0643608990	1	170	2025-04-14 05:15:00	2025-04-14 06:15:00	#54d536	VALIDATED		2	\N	1f000a05-b11b-699c-8cc6-c39afce9e08d	2	f		\N	\N	\N	\N	\N	\N
136	Proto	0782834316	2	140	2025-04-14 05:45:00	2025-04-14 06:35:00	#1c6076	VALIDATED		3	\N	1f000a03-4784-69e0-85cf-c39afce9e08d	4	t		\N	\N	\N	\N	\N	\N
137	Proto	0782834316	2	140	2025-04-14 05:45:00	2025-04-14 06:35:00	#1c6076	VALIDATED		3	\N	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	3	t		\N	\N	\N	\N	\N	\N
138	Chapelle	0781574287	2	170	2025-04-15 05:15:00	2025-04-15 06:15:00	#813aef	VALIDATED		2	\N	1f000a03-4784-69e0-85cf-c39afce9e08d	1	f		\N	\N	\N	\N	\N	\N
139	Chapelle	0781574287	2	170	2025-04-15 05:15:00	2025-04-15 06:15:00	#813aef	VALIDATED		2	\N	1f000a05-b11b-699c-8cc6-c39afce9e08d	2	f		\N	\N	\N	\N	\N	\N
69	FERNANDEZ	+33673712011	2	170	2025-04-19 04:00:00	2025-04-19 05:00:00	#d09fc4	WHEATER_REPORT		2	\N	1f000a05-b11b-699c-8cc6-c39afce9e08d	2	f		2	f	f	\N	\N	\N
68	FERNANDEZ	+33673712011	2	140	2025-04-19 04:00:00	2025-04-19 04:50:00	#d09fc4	WHEATER_REPORT		3	\N	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	1	f		Leader	f	f	\N	\N	\N
153	Jan Andre		2	210	2025-04-19 05:00:00	2025-04-19 06:15:00	#da2789	WHEATER_REPORT		1	\N	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	1	f		Leader	f	f	\N	\N	\N
154	Jan Andre		2	210	2025-04-19 05:00:00	2025-04-19 06:15:00	#da2789	WHEATER_REPORT		1	\N	1f000a05-b11b-699c-8cc6-c39afce9e08d	2	f		2	f	f	\N	\N	\N
155	Hugues		1	170	2025-04-19 02:45:00	2025-04-19 03:45:00	#21b385	VALIDATED		2	\N	1f000a07-a2bc-6da4-bcac-c39afce9e08d	3	f		-	f	f	\N	\N	\N
150	Guimier	+33617714000	2	210	2025-04-18 05:00:00	2025-04-18 06:15:00	#7dfb1d	VALIDATED		1	\N	1f000a05-b11b-699c-8cc6-c39afce9e08d	1	f		2	f	f	\N	\N	\N
149	Guimier	+33617714000	2	210	2025-04-18 05:00:00	2025-04-18 06:15:00	#7dfb1d	VALIDATED		1	\N	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	2	f		Leader	f	f	\N	\N	\N
147	Solon	0676865902	3	130	2025-04-18 04:00:00	2025-04-18 04:45:00	#4a3a89	VALIDATED		4	\N	1f000a03-4784-69e0-85cf-c39afce9e08d	3	f		Leader	f	f	\N	\N	\N
148	Solon	0676865902	3	130	2025-04-18 04:00:00	2025-04-18 04:45:00	#4a3a89	VALIDATED		4	\N	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	2	f		2	f	f	\N	\N	\N
156	Hugues		1	170	2025-04-19 04:00:00	2025-04-19 05:00:00	#947603	VALIDATED		2	\N	1f000a07-a2bc-6da4-bcac-c39afce9e08d	3	f		-	f	f	\N	\N	\N
157	Hugues		1	110	2025-04-20 02:45:00	2025-04-20 03:45:00	#64f715	VALIDATED		6	\N	1f000a07-a2bc-6da4-bcac-c39afce9e08d	2	f		-	f	f	\N	\N	\N
158	Hugues		1	110	2025-04-20 04:00:00	2025-04-20 05:00:00	#18bc08	VALIDATED		6	\N	1f000a07-a2bc-6da4-bcac-c39afce9e08d	2	f		-	f	f	\N	\N	\N
151	luis	+34667947508	2	130	2025-04-19 02:45:00	2025-04-19 03:30:00	#8ac561	WHEATER_REPORT		4	\N	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	1	f		Leader	f	f	\N	\N	\N
152	luis		2	130	2025-04-19 02:45:00	2025-04-19 03:30:00	#8ac561	WHEATER_REPORT		4	\N	1f000a05-b11b-699c-8cc6-c39afce9e08d	2	f		2	f	f	\N	\N	\N
159	Roophie		2	0	2025-04-20 04:30:00	2025-04-20 05:30:00	#f44b99	WHEATER_REPORT		7	\N	\N	1	f		2	f	f	\N	\N	\N
160	Roophie		2	170	2025-04-20 04:30:00	2025-04-20 05:30:00	#f44b99	WHEATER_REPORT		2	\N	1f000a01-0394-65de-b10d-c39afce9e08d	4	f		Leader	f	f	\N	\N	\N
161	Thymotée		1	135	2025-04-20 06:00:00	2025-04-20 07:00:00	#fa53c1	VALIDATED		5	\N	1f000a01-0394-65de-b10d-c39afce9e08d	4	f		-	f	f	\N	\N	\N
168	Galibert	0680566316	2	170	2025-04-23 04:00:00	2025-04-23 05:00:00	#fad3d3	VALIDATED		2	\N	1f000a03-4784-69e0-85cf-c39afce9e08d	\N	f		Leader	f	f	\N	\N	\N
162	Boris		1	110	2025-04-22 04:00:00	2025-04-22 05:00:00	#c349e8	VALIDATED		6	\N	1f000a05-b11b-699c-8cc6-c39afce9e08d	2	f		-	f	f	\N	\N	\N
169	Galibert	0680566316	2	190	2025-04-23 04:00:00	2025-04-23 05:00:00	#fad3d3	VALIDATED		2	1	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	\N	f		-	f	f	\N	\N	\N
170	Maistret	0626731337	2	140	2025-04-23 05:00:00	2025-04-23 05:50:00	#1ce66	VALIDATED		3	\N	1f000a03-4784-69e0-85cf-c39afce9e08d	\N	f		-	f	f	\N	\N	\N
171	Maistret	0626731337	2	140	2025-04-23 05:00:00	2025-04-23 05:50:00	#1ce66	VALIDATED		3	\N	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	\N	f		-	f	f	\N	\N	\N
165	Moulin	0611995279	2	170	2025-04-22 02:45:00	2025-04-22 03:45:00	#1d0ac	VALIDATED		2	\N	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	1	f		Leader	f	f	\N	\N	\N
166	Moulin	0611995279	2	170	2025-04-22 02:45:00	2025-04-22 03:45:00	#1d0ac	VALIDATED		2	\N	1f000a03-4784-69e0-85cf-c39afce9e08d	2	f		2	f	f	\N	\N	\N
163	Heuze	0611933956	1	170	2025-04-22 04:00:00	2025-04-22 05:00:00	#fabf3e	WHEATER_REPORT		2	\N	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	1	f		-	f	f	\N	\N	\N
184	Sébastien Maillot	0692406298	3	190	2025-04-24 03:15:00	2025-04-24 04:15:00	#31083e	VALIDATED		2	1	\N	\N	f	sebastien.maillot@gmx.fr	\N	f	f	\N	\N	\N
179	Sébastien Maillot	0692406298	3	170	2025-04-26 05:00:00	2025-04-26 06:00:00	#a9cfaf	VALIDATED		2	\N	\N	\N	f	sebastien.maillot@gmx.fr	\N	f	f	\N	\N	\N
180	Sébastien Maillot	0692406298	3	190	2025-04-26 05:00:00	2025-04-26 06:00:00	#a9cfaf	VALIDATED		2	1	\N	\N	f	sebastien.maillot@gmx.fr	\N	f	f	\N	\N	\N
181	Sébastien Maillot	0692406298	3	190	2025-04-26 05:00:00	2025-04-26 06:00:00	#a9cfaf	VALIDATED		2	1	\N	\N	f	sebastien.maillot@gmx.fr	\N	f	f	\N	\N	\N
182	Sébastien Maillot	0692406298	3	170	2025-04-24 03:15:00	2025-04-24 04:15:00	#31083e	VALIDATED		2	\N	\N	\N	f	sebastien.maillot@gmx.fr	\N	f	f	\N	\N	\N
183	Sébastien Maillot	0692406298	3	190	2025-04-24 03:15:00	2025-04-24 04:15:00	#31083e	VALIDATED		2	1	\N	\N	f	sebastien.maillot@gmx.fr	\N	f	f	\N	\N	\N
129	Nicolas	0677181654	1	140	2025-04-14 04:15:00	2025-04-14 05:05:00	#a3e9f6	VALIDATED		3	\N	1f000a03-4784-69e0-85cf-c39afce9e08d	4	t		-	f	f	\N	\N	\N
130	Gil	+33636708141	1	210	2025-04-14 03:00:00	2025-04-14 04:15:00	#56599a	VALIDATED		1	\N	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	3	t		-	f	f	\N	\N	\N
188	Sébastien Maillot	0692406298	1	170	2025-04-25 07:00:00	2025-04-25 08:00:00	#6a7e9c	VALIDATED		2	\N	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	2	f	sebastien.maillot@gmx.fr	-	f	f	\N	\N	\N
48	VANDERBEKE	+33760967795	2	170	2025-04-25 04:30:00	2025-04-25 05:30:00	#946928	VALIDATED		2	\N	1f000a01-0394-65de-b10d-c39afce9e08d	4	t		-	f	f	\N	\N	\N
189	DULAC Daniel	0692406298	1	190	2025-05-02 04:00:00	2025-05-02 05:00:00	#6a72f5	VALIDATED		2	1	1f000a03-4784-69e0-85cf-c39afce9e08d	3	f	sebastien.maillot@gmx.fr	-	f	f	\N	\N	\N
190	Sébastien Maillot	0692406298	1	170	2025-05-21 04:15:00	2025-05-21 05:15:00	#8693ec	VALIDATED		2	\N	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	2	f	sebastien.maillot@gmx.fr	Leader	f	f	\N	\N	\N
191	Sébastien Maillot	0692406298	1	170	2025-05-21 04:15:00	2025-05-21 05:15:00	#375e88	VALIDATED		2	\N	\N	\N	f	sebastien.maillot@gmx.fr	-	t	f	\N	\N	\N
192	Jonathan	0692406298	2	170	2025-05-22 02:30:00	2025-05-22 03:30:00	#db27d0	VALIDATED		2	\N	\N	\N	f	sebastien.maillot@gmx.fr	-	f	f	\N	\N	\N
144	Solon	0676865902	2	130	2025-04-18 03:00:00	2025-04-18 03:45:00	#3ad091	VALIDATED		4	\N	1f000a03-4784-69e0-85cf-c39afce9e08d	3	f		Leader	t	f	\N	RESA-1749311011785-SZHKWY	\N
193	Jonathan	0692406298	2	170	2025-05-22 02:30:00	2025-05-22 03:30:00	#db27d0	VALIDATED		2	\N	\N	\N	f	sebastien.maillot@gmx.fr	-	f	f	\N	\N	\N
145	Solon	0676865902	2	130	2025-04-18 03:00:00	2025-04-18 03:45:00	#3ad091	VALIDATED		4	\N	1f000a05-b11b-699c-8cc6-c39afce9e08d	1	f		2	t	f	\N	RESA-1749311018789-MOR76N	\N
141	Levacher	0648142776	2	170	2025-04-16 02:45:00	2025-04-16 03:45:00	#500fdc	VALIDATED		2	\N	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	4	f		2	t	f	\N	RESA-1749311494147-4Z472T	\N
126	rault	0677796445	2	140	2025-04-16 04:00:00	2025-04-16 04:50:00	#15bc6c	VALIDATED		3	\N	1f000a03-4784-69e0-85cf-c39afce9e08d	1	t		Leader	t	f	\N	\N	PAY-1749372760350-EGAWTH
127	rault	0677796445	2	140	2025-04-16 04:00:00	2025-04-16 04:50:00	#15bc6c	VALIDATED		3	\N	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	4	t		2	t	f	\N	\N	PAY-1749372760350-EGAWTH
194	Rugieri Chloé	0692299004	1	140	2025-07-16 04:00:00	2025-07-16 04:50:00	#3bd4b8	VALIDATED		3	\N	\N	\N	f	sebastien.maillot@gmx.fr	-	f	f	\N	RESA-1749621500702-A9S6WK	\N
195	PAYET Rachel	0692674901	1	170	2025-06-12 03:00:00	2025-06-12 04:00:00	#ca4f62	VALIDATED		2	\N	\N	\N	f	\N	\N	t	f	\N	RESA-1749711289824-NI2MJH	PAY-1749711398705-WNXM8K
208	MOREL Brice	0692406298	2	210	2025-06-29 02:45:00	2025-06-29 04:00:00	#cb4289	VALIDATED		1	\N	\N	\N	f	sebastien.maillot@gmx.fr	\N	t	f	\N	RESA-1751005577759-SP88YN	PAY-1751015874188-BQ5WB3
210	Sébastien Maillot	0692406298	1	230	2025-07-01 04:30:00	2025-07-01 05:45:00	#2ca26d	VALIDATED		1	1	\N	\N	f	sebastien.maillot@gmx.fr	\N	f	f	\N	RESA-1751364329899-EK3Y28	\N
211	Sébastien Maillot	0692406298	1	210	2025-07-01 04:30:00	2025-07-01 05:45:00	#82b83b	VALIDATED		1	\N	\N	\N	f	sebastien.maillot@gmx.fr	\N	f	f	\N	RESA-1751364563932-AROXIQ	\N
212	Angele GRETEL	0692406298	1	170	2025-05-07 02:45:00	2025-05-07 03:45:00	#9d802e	VALIDATED		2	\N	\N	\N	f	sebastien.maillot@gmx.fr	\N	f	f	\N	RESA-1751433452783-XPDMPB	\N
213	Sébastien Maillot	0692406298	1	170	2025-05-07 02:45:00	2025-05-07 03:45:00	#28085c	VALIDATED		2	\N	\N	\N	f	sebastien.maillot@gmx.fr	\N	f	f	\N	RESA-1751433490750-DCM4L4	\N
214	Sébastien Maillot	0692406298	1	170	2025-07-02 02:45:00	2025-07-02 03:45:00	#fcb962	VALIDATED		2	\N	\N	\N	f	sebastien.maillot@gmx.fr	\N	f	f	\N	RESA-1751433544412-282ZTH	\N
284	Alphonse BROWN	0692406298	1	210	2025-07-31 04:15:00	2025-07-31 05:30:00	#f41901	WHEATER_REPORT		1	\N	\N	\N	f	sebastien.maillot@gmx.fr	\N	t	f	\N	RESA-1753948350026-2LNJX5	PAY-1754574886413-T2VP3R
200	LAUGIER Aurore	0692406298	2	189	2025-06-29 07:00:00	2025-06-29 08:15:00	#7784c4	WHEATER_REPORT		1	\N	\N	\N	f	sebastien.maillot@gmx.fr	\N	f	f	\N	RESA-1751002180176-DO5VC6	\N
201	LAUGIER Aurore	0692406298	2	189	2025-06-29 07:00:00	2025-06-29 08:15:00	#7784c4	WHEATER_REPORT		1	\N	\N	\N	f	sebastien.maillot@gmx.fr	\N	f	f	\N	RESA-1751002180176-DO5VC6	\N
199	Jean Claude Chabert	0692406298	1	173	2025-06-29 07:00:00	2025-06-29 08:00:00	#17ab0f	WHEATER_REPORT		2	1	\N	\N	f	sebastien.maillot@gmx.fr	\N	f	f	\N	RESA-1751002101818-PRKULT	\N
198	Hervé DUBOIS	0692406298	1	210	2025-06-29 05:30:00	2025-06-29 06:45:00	#2469da	WHEATER_REPORT		1	\N	\N	\N	f	sebastien.maillot@gmx.fr	\N	f	f	\N	RESA-1750995352969-IXFCEE	\N
197	Sébastien Maillot	0692406298	2	170	2025-06-29 05:30:00	2025-06-29 06:30:00	#ba2ee4	WHEATER_REPORT		2	\N	\N	\N	f	sebastien.maillot@gmx.fr	\N	f	f	\N	RESA-1750995304515-7BD1XK	\N
196	Sébastien Maillot	0692406298	2	170	2025-06-29 05:30:00	2025-06-29 06:30:00	#ba2ee4	WHEATER_REPORT		2	\N	\N	\N	f	sebastien.maillot@gmx.fr	\N	f	f	\N	RESA-1750995304515-7BD1XK	\N
202	Isabelle HENRI	0692406298	3	153	2025-06-29 04:15:00	2025-06-29 05:15:00	#a7558f	WHEATER_REPORT		2	\N	\N	\N	f	sebastien.maillot@gmx.fr	\N	f	f	\N	RESA-1751002419059-9E8ABT	\N
218	Sébastien Maillot	0692406298	1	170	2025-07-03 04:30:00	2025-07-03 05:30:00	#a0b1e4	VALIDATED		2	\N	\N	\N	f	sebastien.maillot@gmx.fr	\N	f	f	\N	RESA-1751433830139-4RO9QY	\N
203	Isabelle HENRI	0692406298	3	153	2025-06-29 04:15:00	2025-06-29 05:15:00	#a7558f	WHEATER_REPORT		2	\N	\N	\N	f	sebastien.maillot@gmx.fr	\N	f	f	\N	RESA-1751002419059-9E8ABT	\N
209	Cécile VITRY	0692406298	1	210	2025-06-29 02:45:00	2025-06-29 04:00:00	#75ace3	VALIDATED		1	\N	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	2	f	sebastien.maillot@gmx.fr	-	t	f	5	RESA-1751006158975-PO87F8	\N
204	Isabelle HENRI	0692406298	3	173	2025-06-29 04:15:00	2025-06-29 05:15:00	#a7558f	WHEATER_REPORT		2	1	\N	\N	f	sebastien.maillot@gmx.fr	-	f	f	\N	RESA-1751002419059-9E8ABT	\N
207	MOREL Brice	0692406298	2	210	2025-06-29 02:45:00	2025-06-29 04:00:00	#cb4289	VALIDATED		1	\N	\N	\N	f	sebastien.maillot@gmx.fr	\N	t	f	\N	RESA-1751005577759-SP88YN	PAY-1751015874188-BQ5WB3
222	Laula ROPIEGA	0692406298	3	170	2025-07-02 05:30:00	2025-07-02 06:30:00	#dbde3a	VALIDATED		2	\N	\N	\N	f	sebastien.maillot@gmx.fr	\N	f	f	\N	RESA-1751434465318-CR00C9	\N
253	Caroline CROSNIER	0692406298	1	170	2025-07-02 06:45:00	2025-07-02 07:45:00	#8312bb	VALIDATED		2	\N	\N	\N	f	sebastien.maillot@gmx.fr	\N	f	f	\N	RESA-1751440933100-ETLLGR	\N
230	Eric Pascal	0692406298	2	130	2025-07-02 07:45:00	2025-07-02 08:30:00	#cea411	VALIDATED		4	\N	\N	\N	f	sebastien.maillot@gmx.fr	\N	f	f	\N	RESA-1751437103128-PIF8EP	\N
242	Stephanie YEUNG	0692406298	1	140	2025-07-02 06:45:00	2025-07-02 07:35:00	#d8c56f	VALIDATED		3	\N	\N	\N	f	sebastien.maillot@gmx.fr	\N	f	f	\N	RESA-1751439290079-SDO4XS	\N
243	Christian LEROUX	0692406298	1	130	2025-07-02 07:45:00	2025-07-02 08:30:00	#c51e	VALIDATED		4	\N	\N	\N	f	sebastien.maillot@gmx.fr	\N	f	f	\N	RESA-1751439331341-5RF9F2	\N
244	Eric Leveneur	0692406298	1	210	2025-07-02 04:00:00	2025-07-02 05:15:00	#bcd6d0	VALIDATED		1	\N	\N	\N	f	sebastien.maillot@gmx.fr	\N	f	f	\N	RESA-1751439376375-7XCUBP	\N
248	Eric Rivière	0692406298	1	130	2025-07-02 07:45:00	2025-07-02 08:30:00	#3cb63c	VALIDATED		4	\N	\N	\N	f	sebastien.maillot@gmx.fr	-	f	f	\N	RESA-1751440014177-SPES2W	\N
257	Sébastien Maillot	0692406298	1	230	2025-07-02 02:45:00	2025-07-02 04:00:00	#b1f38	VALIDATED		1	1	\N	\N	f	sebastien.maillot@gmx.fr	-	f	f	\N	RESA-1751452187228-WKZH2M	\N
251	Anne Valérie SAMELOR	0692406298	1	210	2025-07-02 04:00:00	2025-07-02 05:15:00	#3bd224	VALIDATED		1	\N	\N	\N	f	sebastien.maillot@gmx.fr	\N	f	f	\N	RESA-1751440725833-SMGJN3	\N
254	Mathias BONMALAIS	0692406298	1	170	2025-07-02 05:30:00	2025-07-02 06:30:00	#9255b4	VALIDATED		2	\N	\N	\N	f	sebastien.maillot@gmx.fr	\N	f	f	\N	RESA-1751441138817-UHB3CF	\N
256	Patrice PINGAULT	0692406298	1	170	2025-07-02 06:45:00	2025-07-02 07:45:00	#cbf009	VALIDATED		2	\N	\N	\N	f	sebastien.maillot@gmx.fr	-	f	f	\N	RESA-1751443171739-LB14R4	\N
215	Louise Bouilloux	0692406298	1	170	2025-07-02 04:00:00	2025-07-02 05:00:00	#7b0dcb	VALIDATED		2	\N	\N	\N	f	sebastien.maillot@gmx.fr	-	f	f	\N	RESA-1751433585951-E1IOOS	\N
260	Louise Bouilloux	0692406298	1	210	2025-07-03 04:30:00	2025-07-03 05:45:00	#7189e1	VALIDATED		1	\N	\N	\N	f	sebastien.maillot@gmx.fr	\N	f	f	\N	RESA-1751454015364-UAKHBV	\N
255	Marie-France GUIRAUD	0692406298	1	170	2025-07-02 05:30:00	2025-07-02 06:30:00	#f38836	WHEATER_REPORT		2	\N	\N	\N	f	sebastien.maillot@gmx.fr	-	f	f	\N	RESA-1751441966218-DKQQUC	\N
245	Marcel PAYET	0692406298	1	170	2025-07-03 02:45:00	2025-07-03 03:45:00	#d63935	VALIDATED		2	\N	\N	\N	f	sebastien.maillot@gmx.fr	-	f	f	\N	RESA-1751439464818-L86X36	\N
261	Jean-Max HOAREAU	0692406298	1	170	2025-07-03 04:30:00	2025-07-03 05:30:00	#d2358f	VALIDATED		2	\N	\N	\N	f	sebastien.maillot@gmx.fr	\N	f	f	\N	RESA-1751458830675-UOOSQ0	\N
262	Benjamin BOULANGER	0692406298	1	210	2025-07-03 06:00:00	2025-07-03 07:15:00	#c958e4	VALIDATED		1	\N	\N	\N	f	sebastien.maillot@gmx.fr	\N	f	f	\N	RESA-1751459067099-5AO95M	\N
263	Adeline VION	0692406298	1	170	2025-07-03 06:00:00	2025-07-03 07:00:00	#9eff55	VALIDATED		2	\N	\N	\N	f	sebastien.maillot@gmx.fr	-	f	f	\N	RESA-1751459474069-SSUWR8	\N
264	Caroline CROSNIER	0692406298	1	210	2025-07-03 06:00:00	2025-07-03 07:15:00	#4698f1	VALIDATED		1	\N	\N	\N	f	sebastien.maillot@gmx.fr	\N	f	f	\N	RESA-1751462120051-AB4Y9D	\N
265	Estelle ABLANCOURT	0692406298	1	130	2025-07-03 07:30:00	2025-07-03 08:15:00	#11e9ae	VALIDATED		4	\N	\N	\N	f	sebastien.maillot@gmx.fr	-	f	f	\N	RESA-1751462200123-YWUGQU	\N
289	Albert DUFOSSE	0692406298	3	173	2025-07-31 03:00:00	2025-07-31 04:00:00	#51c44f	WAITING		2	1	\N	\N	f	sebastien.maillot@gmx.fr	-	t	f	9	RESA-1753981353237-22XH0M	\N
290	Albert DUFOSSE	0692406298	3	153	2025-07-31 03:00:00	2025-07-31 04:00:00	#51c44f	WAITING		2	\N	\N	\N	f	sebastien.maillot@gmx.fr	-	t	f	9	RESA-1753981353237-22XH0M	\N
216	Arnaud DUPONT	0692406298	2	209	2025-07-03 02:45:00	2025-07-03 04:00:00	#15f4d8	VALIDATED		1	1	\N	\N	f	sebastien.maillot@gmx.fr	-	f	f	\N	RESA-1751433786467-8RV5WY	\N
266	Emeric TAPACHES	0692406298	1	230	2025-07-03 02:45:00	2025-07-03 04:00:00	#6667dc	VALIDATED		1	1	\N	\N	f	sebastien.maillot@gmx.fr	-	f	f	\N	RESA-1751462342010-WBD791	\N
267	Nadia LIONI	0692406298	1	160	2025-07-03 07:30:00	2025-07-03 08:20:00	#d4a074	VALIDATED		3	1	\N	\N	f	sebastien.maillot@gmx.fr	\N	f	f	\N	RESA-1751462606853-KY0S5W	\N
268	Julien JUCOURT	0692406298	3	170	2025-07-04 02:45:00	2025-07-04 03:45:00	#86eb90	VALIDATED		2	\N	\N	\N	f	sebastien.maillot@gmx.fr	\N	f	f	\N	RESA-1751462672612-RFUL3U	\N
269	Julien JUCOURT	0692406298	3	190	2025-07-04 02:45:00	2025-07-04 03:45:00	#86eb90	VALIDATED		2	1	\N	\N	f	sebastien.maillot@gmx.fr	\N	f	f	\N	RESA-1751462672612-RFUL3U	\N
270	Julien JUCOURT	0692406298	3	190	2025-07-04 02:45:00	2025-07-04 03:45:00	#86eb90	VALIDATED		2	1	\N	\N	f	sebastien.maillot@gmx.fr	\N	f	f	\N	RESA-1751462672612-RFUL3U	\N
271	Sandra MAILLOT	0692406298	2	230	2025-07-04 04:00:00	2025-07-04 05:15:00	#3fa702	VALIDATED		1	1	\N	\N	f	sebastien.maillot@gmx.fr	\N	f	f	\N	RESA-1751462732745-RPIQ3M	\N
272	Sandra MAILLOT	0692406298	2	230	2025-07-04 04:00:00	2025-07-04 05:15:00	#3fa702	VALIDATED		1	1	\N	\N	f	sebastien.maillot@gmx.fr	\N	f	f	\N	RESA-1751462732745-RPIQ3M	\N
273	Jérôme GONTHIER	0692406298	1	126	2025-07-04 04:00:00	2025-07-04 04:50:00	#1a2e5d	VALIDATED		3	\N	\N	\N	f	sebastien.maillot@gmx.fr	\N	f	f	\N	RESA-1751463228799-EOEJK3	\N
274	Marie-France DE-GUIGNÉ	0692406298	1	210	2025-07-04 05:30:00	2025-07-04 06:45:00	#1bfe57	VALIDATED		1	\N	\N	\N	f	sebastien.maillot@gmx.fr	\N	f	f	\N	RESA-1751467498851-4KKB9O	\N
275	BATTY Jean-Michel	0692406298	2	170	2025-07-04 05:30:00	2025-07-04 06:30:00	#647c39	VALIDATED		2	2	\N	\N	f	sebastien.maillot@gmx.fr	\N	f	f	\N	RESA-1751467564344-8W3SVW	\N
276	BATTY Jean-Michel	0692406298	2	170	2025-07-04 05:30:00	2025-07-04 06:30:00	#647c39	VALIDATED		2	\N	\N	\N	f	sebastien.maillot@gmx.fr	\N	f	f	\N	RESA-1751467564344-8W3SVW	\N
277	Sébastien Maillot	0692406298	3	210	2025-07-04 07:00:00	2025-07-04 08:15:00	#f84e41	VALIDATED		1	\N	\N	\N	f	sebastien.maillot@gmx.fr	\N	f	f	\N	RESA-1751468106995-I4ICJK	\N
278	Sébastien Maillot	0692406298	3	210	2025-07-04 07:00:00	2025-07-04 08:15:00	#f84e41	VALIDATED		1	\N	\N	\N	f	sebastien.maillot@gmx.fr	\N	f	f	\N	RESA-1751468106995-I4ICJK	\N
279	Sébastien Maillot	0692406298	3	210	2025-07-04 07:00:00	2025-07-04 08:15:00	#f84e41	VALIDATED		1	\N	\N	\N	f	sebastien.maillot@gmx.fr	\N	f	f	\N	RESA-1751468106995-I4ICJK	\N
291	Albert DUFOSSE	0692406298	3	153	2025-07-31 03:00:00	2025-07-31 04:00:00	#51c44f	WAITING		2	\N	\N	\N	f	sebastien.maillot@gmx.fr	-	t	f	9	RESA-1753981353237-22XH0M	\N
295	Beneficiaire		1	153	2025-08-08 05:30:00	2025-08-08 06:30:00	#7ed6ea	VALIDATED		2	\N	\N	\N	f	sebastien.maillot@gmx.fr	\N	t	f	8	RESA-1754576879331-0VMK0K	PAY-1754576938345-7XJ25Q
297	Sébastien WADOUX	0692406298	1	230	2025-08-12 04:30:00	2025-08-12 05:45:00	#51bfeb	VALIDATED		1	1	\N	\N	f	sebastien.maillot@gmx.fr	-	t	f	13	RESA-1755011975837-XN70NX	\N
283	Johnny HALLIDAY	0692406298	1	170	2025-07-30 04:00:00	2025-07-30 05:00:00	#55010d	WHEATER_REPORT		2	\N	\N	\N	t	sebastien.maillot@gmx.fr	\N	t	f	\N	RESA-1753807546261-VSDWGK	PAY-1753901205888-J21X8G
280	Sébastien Maillot	0692406298	3	189	2025-07-30 02:30:00	2025-07-30 03:45:00	#37d020	WHEATER_REPORT		1	\N	\N	\N	f	sebastien.maillot@gmx.fr	-	t	f	7	RESA-1753768453660-RWDN3T	PAY-1753903575286-5Y9UGM
281	Sébastien Maillot	0692406298	3	209	2025-07-30 02:30:00	2025-07-30 03:45:00	#37d020	WEATHER_REPORT		1	1	\N	\N	f	sebastien.maillot@gmx.fr	-	t	f	7	RESA-1753768453660-RWDN3T	PAY-1753903575286-5Y9UGM
282	Sébastien Maillot	0692406298	3	189	2025-07-30 04:00:00	2025-07-30 05:15:00	#37d020	WHEATER_REPORT		1	\N	\N	\N	f	sebastien.maillot@gmx.fr	-	t	f	7	RESA-1753768453660-RWDN3T	PAY-1753903575286-5Y9UGM
292	Antoine DUPONT	0692406298	2	153	2025-08-01 06:00:00	2025-08-01 07:00:00	#4c59ad	VALIDATED		2	\N	\N	\N	f	sebastien.maillot@gmx.fr	\N	t	f	10	RESA-1754061613932-3LT827	\N
294	Sébastien Chabal	0692406298	1	210	2025-08-01 06:00:00	2025-08-01 07:15:00	#91a209	VALIDATED		1	\N	\N	\N	f	sebastien.maillot@gmx.fr	\N	t	f	11	RESA-1754061737520-K4UF3N	\N
299	Sébastien MAILLOT		1	190	2025-08-12 04:30:00	2025-08-12 05:30:00	#84312d	VALIDATED		2	1	\N	\N	f	sebastien.maillot@gmx.fr	\N	t	f	6	RESA-1755013105802-1B4FKO	\N
300	Albert Devilliers	0692406298	2	135	2025-08-19 04:30:00	2025-08-19 05:00:00	#91ded7	VALIDATED		11	\N	1f000a03-4784-69e0-85cf-c39afce9e08d	2	f	sebastien.maillot@gmx.fr	-	f	f	\N	RESA-1755613284743-B8OF9M	\N
296	Sébastien Maillot	0692406298	1	210	2025-08-19 04:30:00	2025-08-19 05:45:00	#34848b	VALIDATED		1	\N	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	2	f	sebastien.maillot@gmx.fr	-	f	f	\N	RESA-1755011871730-3IN6ZS	\N
302	Jean DUJARDIN	0692406298	2	153	2025-08-19 06:30:00	2025-08-19 07:30:00	#7cf4b4	VALIDATED		2	\N	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	2	f	sebastien.maillot@gmx.fr	-	f	f	\N	RESA-1755613550482-LDR7HI	\N
303	Jean DUJARDIN	0692406298	2	121.5	2025-08-19 06:30:00	2025-08-19 07:30:00	#7cf4b4	VALIDATED		5	\N	1f000a03-4784-69e0-85cf-c39afce9e08d	4	f	sebastien.maillot@gmx.fr	-	f	f	\N	RESA-1755613550482-LDR7HI	\N
301	Albert Devilliers	0692406298	2	170	2025-08-19 04:30:00	2025-08-19 05:30:00	#91ded7	VALIDATED		2	\N	1f000a01-0394-65de-b10d-c39afce9e08d	3	f	sebastien.maillot@gmx.fr	-	f	f	\N	RESA-1755613284743-B8OF9M	\N
305	Jêrome GONTHIER	0692406298	2	153	2025-08-20 03:00:00	2025-08-20 04:00:00	#353639	VALIDATED		2	\N	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	3	f	sebastien.maillot@gmx.fr	Leader	f	f	\N	RESA-1755706184287-IEE17J	\N
304	Jêrome GONTHIER	0692406298	2	173	2025-08-20 03:00:00	2025-08-20 04:00:00	#353639	VALIDATED		2	1	1f000a01-0394-65de-b10d-c39afce9e08d	4	f	sebastien.maillot@gmx.fr	2	f	f	\N	RESA-1755706184287-IEE17J	\N
306	Julien DORÉ	0692406298	1	170	2025-08-20 03:00:00	2025-08-20 04:00:00	#4a9496	VALIDATED		2	\N	1f000a03-4784-69e0-85cf-c39afce9e08d	2	f	sebastien.maillot@gmx.fr	-	f	f	\N	RESA-1756898289060-B6WHN3	\N
310	Sébastien Maillot	0692406298	1	210	2025-09-03 03:00:00	2025-09-03 04:15:00	#35db9b	VALIDATED		1	\N	\N	\N	f	sebastien.maillot@gmx.fr	\N	f	f	\N	RESA-1756923182098-XE08A8	\N
311	Pierre Maillot	0692406298	1	210	2025-09-04 03:00:00	2025-09-04 04:15:00	#c5465e	VALIDATED		1	\N	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	2	f	sebastien.maillot@gmx.fr	-	f	f	\N	RESA-1756923206021-J3E338	\N
312	Harry COT	0692406298	2	210	2025-09-04 03:00:00	2025-09-04 04:15:00	#e970aa	WHEATER_REPORT		1	\N	1f000a03-4784-69e0-85cf-c39afce9e08d	4	f	sebastien.maillot@gmx.fr	-	f	f	\N	RESA-1757007043285-X9TZMT	\N
313	Harry COT	0692406298	2	210	2025-09-04 03:00:00	2025-09-04 04:15:00	#e970aa	WHEATER_REPORT		1	\N	1f000a05-b11b-699c-8cc6-c39afce9e08d	1	f	sebastien.maillot@gmx.fr	-	f	f	\N	RESA-1757007043285-X9TZMT	\N
344	Sébastien Maillot	0692406298	\N	230	2025-09-13 03:15:00	2025-09-13 04:30:00	#5af1c1	WHEATER_REPORT		1	1	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	2	f	sebastien.maillot@gmx.fr	-	f	f	\N	RESA-1757767976879-3YG2DP	\N
314	Olivier DECHAMPS	0692406298	3	173	2025-09-04 04:30:00	2025-09-04 05:30:00	#acf357	VALIDATED		2	1	1f000a05-b11b-699c-8cc6-c39afce9e08d	1	f	sebastien.maillot@gmx.fr	-	f	f	\N	RESA-1757008093964-GC72G6	\N
315	Olivier DECHAMPS	0692406298	3	153	2025-09-04 04:30:00	2025-09-04 05:30:00	#acf357	VALIDATED		2	\N	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	2	f	sebastien.maillot@gmx.fr	-	f	f	\N	RESA-1757008093964-GC72G6	\N
316	Olivier DECHAMPS	0692406298	3	153	2025-09-04 04:30:00	2025-09-04 05:30:00	#acf357	VALIDATED		2	\N	1f000a03-4784-69e0-85cf-c39afce9e08d	4	f	sebastien.maillot@gmx.fr	-	f	f	\N	RESA-1757008093964-GC72G6	\N
346	Bernard Arnaud	0692406298	\N	189	2025-09-13 03:15:00	2025-09-13 04:30:00	#810af7	WHEATER_REPORT		1	\N	1f000a05-b11b-699c-8cc6-c39afce9e08d	3	f	sebastien.maillot@gmx.fr	-	f	f	\N	RESA-1757770429447-O4SJVV	\N
345	Lionel JOSPIN	0692406298	\N	210	2025-09-13 03:30:00	2025-09-13 04:45:00	#45b233	WHEATER_REPORT		1	\N	1f000a03-4784-69e0-85cf-c39afce9e08d	4	t	sebastien.maillot@gmx.fr	-	f	f	\N	RESA-1757770387531-813XTD	\N
319	Marjorie Bodinger	0692406298	3	210	2025-09-05 03:00:00	2025-09-05 04:15:00	#b6355b	VALIDATED		1	\N	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	2	f	sebastien.maillot@gmx.fr	2	f	f	\N	RESA-1757085652834-UCQSEF	\N
318	Marjorie Bodinger	0692406298	3	230	2025-09-05 03:00:00	2025-09-05 04:15:00	#b6355b	VALIDATED		1	1	1f000a05-b11b-699c-8cc6-c39afce9e08d	1	f	sebastien.maillot@gmx.fr	3	f	f	\N	RESA-1757085652834-UCQSEF	\N
317	Marjorie Bodinger	0692406298	3	210	2025-09-05 03:00:00	2025-09-05 04:15:00	#b6355b	VALIDATED		1	\N	1f000a03-4784-69e0-85cf-c39afce9e08d	4	f	sebastien.maillot@gmx.fr	Leader	f	f	\N	RESA-1757085652834-UCQSEF	\N
320	Dany X le Hardy	0692406298	1	153	2025-09-06 04:15:00	2025-09-06 05:15:00	#d21b93	VALIDATED		2	1	\N	\N	f	sebastien.maillot@gmx.fr	\N	t	f	18	RESA-1757086103636-MSX80Y	\N
321	Marcus Gronholm	0692406298	1	153	2025-09-06 04:15:00	2025-09-06 05:15:00	#33ef60	VALIDATED		2	1	\N	\N	f	sebastien.maillot@gmx.fr	\N	t	f	19	RESA-1757086155080-B4C8OX	\N
322	Stella Grondin	0692406298	2	210	2025-09-06 02:45:00	2025-09-06 04:00:00	#36aedf	WHEATER_REPORT		1	\N	\N	\N	f	sebastien.maillot@gmx.fr	-	t	f	20	RESA-1757086197982-HIZ7L1	\N
323	Stella Grondin	0692406298	2	210	2025-09-06 02:45:00	2025-09-06 04:00:00	#36aedf	WHEATER_REPORT		1	\N	\N	\N	f	sebastien.maillot@gmx.fr	-	t	f	20	RESA-1757086197982-HIZ7L1	\N
324	Jonathan PAIUS	0692406298	1	230	2025-09-06 02:45:00	2025-09-06 04:00:00	#20adc1	VALIDATED		1	1	1f000a05-b11b-699c-8cc6-c39afce9e08d	1	f	sebastien.maillot@gmx.fr	3	f	f	\N	RESA-1757595796861-SD0ACV	\N
325	Gilbert POUNIA	0692406298	2	170	2025-09-11 03:00:00	2025-09-11 04:00:00	#56bd5a	VALIDATED		2	\N	1f000a03-4784-69e0-85cf-c39afce9e08d	1	f	sebastien.maillot@gmx.fr	Leader	f	f	\N	RESA-1757595883623-3L7SEB	\N
326	Gilbert POUNIA	0692406298	2	170	2025-09-11 03:00:00	2025-09-11 04:00:00	#56bd5a	VALIDATED		2	\N	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	2	f	sebastien.maillot@gmx.fr	2	f	f	\N	RESA-1757595883623-3L7SEB	\N
327	Hervé LEONARD		1	170	2025-09-11 04:30:00	2025-09-11 05:30:00	#f717b1	VALIDATED		2	\N	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	2	f	sebastien.maillot@gmx.fr	\N	t	f	3	RESA-1757600898606-4HQXZC	\N
329	Jérémy SAKSICK	0692406298	1	230	2025-09-11 03:00:00	2025-09-11 04:15:00	#38d407	VALIDATED		1	1	1f000a05-b11b-699c-8cc6-c39afce9e08d	3	f	sebastien.maillot@gmx.fr	3	f	f	\N	RESA-1757605994436-JH5WE0	\N
330	Sébastien Maillot	0692406298	1	190	2025-09-11 04:30:00	2025-09-11 05:30:00	#3fa877	VALIDATED		2	1	1f000a05-b11b-699c-8cc6-c39afce9e08d	3	f	sebastien.maillot@gmx.fr	2	f	f	\N	RESA-1757606141046-XHB6A1	\N
331	Sébastien Maillot	0692406298	2	189	2025-09-11 05:45:00	2025-09-11 07:00:00	#73cabb	WHEATER_REPORT		1	\N	\N	\N	f	sebastien.maillot@gmx.fr	-	f	f	\N	RESA-1757608649704-9SKIM3	\N
332	Sébastien Maillot	0692406298	2	189	2025-09-11 05:45:00	2025-09-11 07:00:00	#73cabb	WHEATER_REPORT		1	\N	\N	\N	f	sebastien.maillot@gmx.fr	-	f	f	\N	RESA-1757608649704-9SKIM3	\N
333	Sébastien Maillot	0692406298	1	170	2025-09-11 04:30:00	2025-09-11 05:30:00	#b2c228	VALIDATED		2	\N	1f000a03-4784-69e0-85cf-c39afce9e08d	4	f	sebastien.maillot@gmx.fr	3	f	f	\N	RESA-1757608760543-XJNQBX	\N
352	RETAILLEAU Bruno	0692406298	2	170	2025-09-13 06:30:00	2025-09-13 07:30:00	#7e6818	VALIDATED		2	\N	1f000a03-4784-69e0-85cf-c39afce9e08d	4	f	sebastien.maillot@gmx.fr	2	f	f	\N	RESA-1757771115383-Q2JH3W	\N
334	Didier HUDRY	0674983241	2	210	2025-09-12 05:30:00	2025-09-12 06:45:00	#7f1e57	VALIDATED		1	\N	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	2	f	didier.hudry@gmail.com	Leader	t	f	16	RESA-1757659951311-QZEF9W	\N
335	Didier HUDRY	0674983241	2	210	2025-09-12 05:30:00	2025-09-12 06:45:00	#7f1e57	VALIDATED		1	\N	1f000a05-b11b-699c-8cc6-c39afce9e08d	3	f	didier.hudry@gmail.com	2	t	f	16	RESA-1757659951311-QZEF9W	\N
336	ULA Raymonde	0692406298	1	189	2025-09-12 05:30:00	2025-09-12 06:45:00	#e94137	WHEATER_REPORT		1	\N	1f000a03-4784-69e0-85cf-c39afce9e08d	4	f	sebastien.maillot@gmx.fr	3	f	f	\N	RESA-1757660043479-UVUZ4M	\N
337	Didier HUDRY	0674983241	1	210	2025-09-12 03:00:00	2025-09-12 04:15:00	#5b96d0	VALIDATED		1	1	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	2	f	didier.hudry@gmail.com	-	t	f	17	RESA-1757660106283-292RXX	\N
338	Elelyne THOMAS	0692406298	\N	173	2025-09-12 03:00:00	2025-09-12 04:00:00	#9096cc	VALIDATED		2	1	1f000a05-b11b-699c-8cc6-c39afce9e08d	3	f	sebastien.maillot@gmx.fr	-	f	f	\N	RESA-1757660286569-C7VFF9	\N
340	Patrick BERTRAND	0692406298	\N	170	2025-09-12 04:15:00	2025-09-12 05:15:00	#cc3f7	WHEATER_REPORT		2	\N	\N	\N	f	sebastien.maillot@gmx.fr	\N	f	f	\N	RESA-1757660339155-8REKZ4	\N
339	Patrick BERTRAND	0692406298	\N	190	2025-09-12 04:15:00	2025-09-12 05:15:00	#cc3f7	WHEATER_REPORT		2	1	\N	\N	f	sebastien.maillot@gmx.fr	\N	f	f	\N	RESA-1757660339155-8REKZ4	\N
341	Amina LALA		1	170	2025-09-12 04:15:00	2025-09-12 05:15:00	#b025ce	VALIDATED		2	\N	1f000a03-4784-69e0-85cf-c39afce9e08d	4	f	sebastien.maillot@gmx.fr	Leader	t	f	4	RESA-1757660410224-IGXCV7	\N
342	Louise BOUILLOUX	0692406298	1	153	2025-09-12 07:00:00	2025-09-12 08:00:00	#3bd18a	VALIDATED		2	1	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	2	f	sebastien.maillot@gmx.fr	-	t	f	12	RESA-1757662734203-ZX1MID	\N
343	Sébastien MAILLOT		1	210	2025-09-12 07:00:00	2025-09-12 08:15:00	#d46f81	VALIDATED		1	\N	1f000a03-4784-69e0-85cf-c39afce9e08d	4	f	sebastien.maillot@gmx.fr	Leader	t	f	2	RESA-1757663084985-E526XF	\N
347	Jacques CHIRAC	0692406298	\N	189	2025-09-13 05:00:00	2025-09-13 06:15:00	#1dab8d	VALIDATED		1	\N	1f000a03-4784-69e0-85cf-c39afce9e08d	4	f	sebastien.maillot@gmx.fr	Leader	f	f	\N	RESA-1757770641670-IGMMGO	\N
349	Jacques CHIRAC	0692406298	\N	189	2025-09-13 05:00:00	2025-09-13 06:15:00	#1dab8d	VALIDATED		1	\N	1f000a05-b11b-699c-8cc6-c39afce9e08d	1	f	sebastien.maillot@gmx.fr	3	f	f	\N	RESA-1757770641670-IGMMGO	\N
348	Jacques CHIRAC	0692406298	\N	209	2025-09-13 05:00:00	2025-09-13 06:15:00	#1dab8d	VALIDATED		1	1	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	2	f	sebastien.maillot@gmx.fr	2	f	f	\N	RESA-1757770641670-IGMMGO	\N
353	Sébastien Maillot	0692406298	\N	210	2025-09-13 02:00:00	2025-09-13 03:15:00	#ea4a5c	VALIDATED		1	\N	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	2	f	sebastien.maillot@gmx.fr	Leader	f	f	\N	RESA-1757779612738-G5M6IA	\N
350	BAYROU François	0692406298	1	153	2025-09-13 06:30:00	2025-09-13 07:30:00	#d9cad9	VALIDATED		2	\N	1f08e51e-d4bb-6e5e-ac48-c9cbe02f0cce	1	f	sebastien.maillot@gmx.fr	3	f	f	\N	RESA-1757771008670-HSDE7H	\N
355	Louise BOUILLOUX	0692406298	1	153	2025-09-13 10:00:00	2025-09-13 11:00:00	#f0963e	VALIDATED		2	\N	1f000a03-4784-69e0-85cf-c39afce9e08d	4	f	sebastien.maillot@gmx.fr	-	t	f	\N	RESA-1757784856346-23VV1Q	PAY-1757862988303-ZPSBBP
354	Sébastien Maillot	0692406298	1	189	2025-09-13 10:00:00	2025-09-13 11:15:00	#97a3b3	VALIDATED		1	\N	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	2	f	sebastien.maillot@gmx.fr	-	t	f	\N	RESA-1757784780995-Q8IHAN	PAY-1757863237013-B7LL75
376	Fernando ALONSO	0692 40 62 98	1	173	2025-09-15 03:00:00	2025-09-15 04:00:00	#5afeb2	VALIDATED		2	1	1f000a05-b11b-699c-8cc6-c39afce9e08d	3	f	sebastien.maillot@gmx.fr	-	t	f	29	RESA-1757915520847-YNL3C8	\N
357	Amélie MAURESMO	0692 40 62 98	\N	170	2025-09-13 02:15:00	2025-09-13 03:15:00	#d0205f	VALIDATED		2	\N	1f000a03-4784-69e0-85cf-c39afce9e08d	3	f	sebastien.maillot@gmx.fr	Leader	f	f	\N	RESA-1757871771275-PK6BDH	\N
358	Jean-Pierre PAPIN	0692 40 62 98	1	210	2025-09-13 02:15:00	2025-09-13 03:30:00	#247d18	VALIDATED		1	1	1f08e51e-d4bb-6e5e-ac48-c9cbe02f0cce	1	f	sebastien.maillot@gmx.fr	3	t	f	21	RESA-1757871896784-KH24P2	\N
362	Pierre GASLY	0692 40 62 98	1	189	2025-09-14 05:45:00	2025-09-14 07:00:00	#7d5182	VALIDATED		1	2	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	2	f	sebastien.maillot@gmx.fr	\N	t	f	25	RESA-1757875312567-77935M	PAY-1757880855845-1WNWES
377	Gabriel BARTOLETTO	0692 40 62 98	1	173	2025-09-15 03:00:00	2025-09-15 04:00:00	#bfe35a	VALIDATED		2	1	1f000a03-4784-69e0-85cf-c39afce9e08d	4	f	sebastien.maillot@gmx.fr	-	t	f	28	RESA-1757915679496-FMGA9D	\N
365	Yuki TSUNODA	0692 40 62 98	1	170	2025-09-14 07:15:00	2025-09-14 08:15:00	#734c84	WHEATER_REPORT		2	1	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	2	f	sebastien.maillot@gmx.fr	-	t	f	27	RESA-1757881494659-72JKPI	\N
374	Esteban OCON	0692 40 62 98	2	189	2025-09-15 05:30:00	2025-09-15 06:45:00	#18a76b	VALIDATED		1	\N	\N	\N	f	sebastien.maillot@gmx.fr	-	t	f	26	RESA-1757883029002-809J39	\N
375	Esteban OCON	0692 40 62 98	2	209	2025-09-15 05:30:00	2025-09-15 06:45:00	#18a76b	VALIDATED		1	1	\N	\N	f	sebastien.maillot@gmx.fr	-	t	f	26	RESA-1757883029002-809J39	\N
372	Helmut Marko	0692 40 62 98	2	173	2025-09-15 04:15:00	2025-09-15 05:15:00	#8db12	VALIDATED		2	1	\N	\N	f	sebastien.maillot@gmx.fr	\N	t	f	24	RESA-1757882938978-6PQ49U	\N
373	Helmut Marko	0692 40 62 98	2	153	2025-09-15 04:15:00	2025-09-15 05:15:00	#8db12	VALIDATED		2	\N	\N	\N	f	sebastien.maillot@gmx.fr	\N	t	f	24	RESA-1757882938978-6PQ49U	\N
361	Thierry HENRI	0692 40 62 98	1	209	2025-09-14 04:30:00	2025-09-14 05:45:00	#b5d753	VALIDATED		1	1	1effe8dc-f73d-600a-bd2c-3324ddd39a2e	2	f	sebastien.maillot@gmx.fr	2	t	f	23	RESA-1757875138674-4O3Z8F	PAY-1757883152504-515VPC
356	Ludovic BOYER	0692406298	\N	210	2025-09-13 10:00:00	2025-09-13 11:15:00	#853a44	VALIDATED		1	\N	1f000a05-b11b-699c-8cc6-c39afce9e08d	3	f	sebastien.maillot@gmx.fr	3	t	f	\N	RESA-1757784978513-BD1A25	PAY-1757862819250-OCV5QZ
\.


--
-- Data for Name: reservation_contact; Type: TABLE DATA; Schema: public; Owner: app
--

COPY public.reservation_contact (reservation_id, contact_id) FROM stdin;
179	5
180	5
181	5
182	5
183	5
184	5
188	5
189	1
189	4
194	2
195	1
197	5
196	5
199	1
200	2
201	2
202	1
203	1
204	1
207	5
208	5
209	1
216	2
222	5
256	3
264	5
266	1
267	1
268	1
269	1
270	1
273	1
274	1
275	1
276	1
280	5
284	1
302	2
303	2
304	1
305	1
306	1
315	1
314	1
316	1
318	1
319	1
317	1
324	1
325	1
326	1
329	1
330	4
331	1
332	1
333	2
336	1
338	1
339	2
340	2
344	2
345	1
346	1
347	1
348	1
349	1
350	2
351	1
352	1
351	2
352	2
353	1
354	1
355	1
356	1
357	1
356	5
356	3
\.


--
-- Data for Name: reservation_origine; Type: TABLE DATA; Schema: public; Owner: app
--

COPY public.reservation_origine (reservation_id, origine_id) FROM stdin;
373	9
373	7
373	5
372	9
372	7
372	5
180	5
179	5
181	5
182	5
183	5
184	5
375	4
375	7
375	9
188	5
189	6
194	7
195	7
197	10
196	10
199	9
200	8
201	8
202	9
203	9
204	9
374	4
374	7
207	5
208	5
209	5
374	9
216	9
222	5
376	7
376	9
256	5
264	5
266	7
267	7
268	5
269	5
270	5
273	9
274	5
275	5
276	5
377	4
281	9
280	9
282	9
284	1
377	9
289	8
290	8
291	8
292	9
293	9
294	7
295	8
297	5
303	7
302	7
302	9
303	9
304	9
305	9
306	7
316	9
314	9
315	9
318	7
319	7
317	7
320	8
321	8
322	7
323	7
324	5
325	6
326	6
327	7
329	7
330	1
331	9
332	9
333	7
334	7
335	7
336	5
336	9
337	7
338	9
339	7
340	7
341	5
342	7
342	9
343	5
344	7
346	8
347	9
348	9
349	9
350	7
351	7
352	2
347	5
348	7
349	7
353	7
345	1
345	5
354	9
354	5
355	9
350	9
356	6
355	5
355	4
357	6
357	7
358	6
358	5
361	7
361	9
362	7
362	8
361	5
365	7
\.


--
-- Name: aeronef_id_seq; Type: SEQUENCE SET; Schema: public; Owner: app
--

SELECT pg_catalog.setval('public.aeronef_id_seq', 6, true);


--
-- Name: airport_id_seq; Type: SEQUENCE SET; Schema: public; Owner: app
--

SELECT pg_catalog.setval('public.airport_id_seq', 9, true);


--
-- Name: cadeau_id_seq; Type: SEQUENCE SET; Schema: public; Owner: app
--

SELECT pg_catalog.setval('public.cadeau_id_seq', 29, true);


--
-- Name: camera_id_seq; Type: SEQUENCE SET; Schema: public; Owner: app
--

SELECT pg_catalog.setval('public.camera_id_seq', 25, true);


--
-- Name: carnet_vol_id_seq; Type: SEQUENCE SET; Schema: public; Owner: app
--

SELECT pg_catalog.setval('public.carnet_vol_id_seq', 398, true);


--
-- Name: certificat_medical_id_seq; Type: SEQUENCE SET; Schema: public; Owner: app
--

SELECT pg_catalog.setval('public.certificat_medical_id_seq', 35, true);


--
-- Name: circuit_id_seq; Type: SEQUENCE SET; Schema: public; Owner: app
--

SELECT pg_catalog.setval('public.circuit_id_seq', 13, true);


--
-- Name: client_id_seq; Type: SEQUENCE SET; Schema: public; Owner: app
--

SELECT pg_catalog.setval('public.client_id_seq', 34, true);


--
-- Name: combinaison_id_seq; Type: SEQUENCE SET; Schema: public; Owner: app
--

SELECT pg_catalog.setval('public.combinaison_id_seq', 10, true);


--
-- Name: contact_id_seq; Type: SEQUENCE SET; Schema: public; Owner: app
--

SELECT pg_catalog.setval('public.contact_id_seq', 5, true);


--
-- Name: disponibilite_id_seq; Type: SEQUENCE SET; Schema: public; Owner: app
--

SELECT pg_catalog.setval('public.disponibilite_id_seq', 3, true);


--
-- Name: entretien_id_seq; Type: SEQUENCE SET; Schema: public; Owner: app
--

SELECT pg_catalog.setval('public.entretien_id_seq', 7, true);


--
-- Name: expense_id_seq; Type: SEQUENCE SET; Schema: public; Owner: app
--

SELECT pg_catalog.setval('public.expense_id_seq', 2, true);


--
-- Name: landing_id_seq; Type: SEQUENCE SET; Schema: public; Owner: app
--

SELECT pg_catalog.setval('public.landing_id_seq', 162, true);


--
-- Name: media_object_id_seq; Type: SEQUENCE SET; Schema: public; Owner: app
--

SELECT pg_catalog.setval('public.media_object_id_seq', 63, true);


--
-- Name: nature_id_seq; Type: SEQUENCE SET; Schema: public; Owner: app
--

SELECT pg_catalog.setval('public.nature_id_seq', 6, true);


--
-- Name: option_id_seq; Type: SEQUENCE SET; Schema: public; Owner: app
--

SELECT pg_catalog.setval('public.option_id_seq', 5, true);


--
-- Name: origine_id_seq; Type: SEQUENCE SET; Schema: public; Owner: app
--

SELECT pg_catalog.setval('public.origine_id_seq', 10, true);


--
-- Name: passager_id_seq; Type: SEQUENCE SET; Schema: public; Owner: app
--

SELECT pg_catalog.setval('public.passager_id_seq', 65, true);


--
-- Name: payment_detail_id_seq; Type: SEQUENCE SET; Schema: public; Owner: app
--

SELECT pg_catalog.setval('public.payment_detail_id_seq', 50, true);


--
-- Name: payment_id_seq; Type: SEQUENCE SET; Schema: public; Owner: app
--

SELECT pg_catalog.setval('public.payment_id_seq', 33, true);


--
-- Name: pilot_qualification_id_seq; Type: SEQUENCE SET; Schema: public; Owner: app
--

SELECT pg_catalog.setval('public.pilot_qualification_id_seq', 51, true);


--
-- Name: prestation_id_seq; Type: SEQUENCE SET; Schema: public; Owner: app
--

SELECT pg_catalog.setval('public.prestation_id_seq', 146, true);


--
-- Name: profil_pilote_id_seq; Type: SEQUENCE SET; Schema: public; Owner: app
--

SELECT pg_catalog.setval('public.profil_pilote_id_seq', 45, true);


--
-- Name: qualification_id_seq; Type: SEQUENCE SET; Schema: public; Owner: app
--

SELECT pg_catalog.setval('public.qualification_id_seq', 9, true);


--
-- Name: rappel_id_seq; Type: SEQUENCE SET; Schema: public; Owner: app
--

SELECT pg_catalog.setval('public.rappel_id_seq', 10, true);


--
-- Name: reservation_id_seq; Type: SEQUENCE SET; Schema: public; Owner: app
--

SELECT pg_catalog.setval('public.reservation_id_seq', 377, true);


--
-- Name: vol_id_seq; Type: SEQUENCE SET; Schema: public; Owner: app
--

SELECT pg_catalog.setval('public.vol_id_seq', 212, true);


--
-- PostgreSQL database dump complete
--

