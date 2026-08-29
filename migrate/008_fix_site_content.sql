
SET @wid = '775d64f8-99a0-11f1-bc37-06bd6669bc40';
DELETE FROM site_content WHERE id = '698d3369-0155-4fd7-b0da-89c72c2dd859'; 
DELETE FROM site_content WHERE id = 'ae80012a-d265-4dbe-ba48-e23f12456e9d'; 
UPDATE site_content SET content_text = 'Neem contact op'
  WHERE website_id = @wid AND page = 'contact' AND section_key = 'contact_hero_title';

UPDATE site_content SET content_text = 'Heeft u een vraag over onze services of wilt u de voorraad van een artikel checken?'
  WHERE website_id = @wid AND page = 'contact' AND section_key = 'contact_hero_text';

UPDATE site_content SET content_text = 'https://www.facebook.com/BandijkGoor/?locale=nl_NL'
  WHERE website_id = @wid AND page = 'algemeen' AND section_key = 'social_facebook';

INSERT INTO site_content (id, website_id, page, section_key, content_text, type, label, group_name, is_visible) VALUES
(UUID(), @wid, 'about', 'about_hero_title', 'Het verhaal achter De Bandijk', 'text', 'Titel bovenaan de Over Ons-pagina', 'hero', 1),
(UUID(), @wid, 'about', 'about_intro_text', 'Bij Primera De Bandijk geloven we in de kracht van persoonlijk contact en ouderwetse service in een moderne jas. Ontdek het verhaal en de mensen achter uw favoriete gemakswinkel in Goor.', 'textarea', 'Introtekst naast de foto', 'hero', 1),
(UUID(), @wid, 'about', 'about_story_title', 'Gewoon gezellig bij De Bandijk', 'text', 'Titel van het verhaal', 'story', 1),
(UUID(), @wid, 'about', 'about_story_1', 'Al drie decennia lang draait het bij ons om meer dan alleen de verkoop. René en Gerri legden in ''94 de basis, en die passie hebben ze overgedragen aan de volgende generatie. Dat René ondanks zijn oogziekte altijd vol gas is blijven geven, tekent de sfeer van onze zaak: we kijken naar wat wél kan.', 'textarea', 'Verhaal - alinea 1', 'story', 1),
(UUID(), @wid, 'about', 'about_story_2', 'Inmiddels hebben René en Cindy het stokje van de ''oude garde'' overgenomen. Samen met ons team zijn we uitgegroeid tot de plek in Goor waar je niet alleen komt voor je zaken, maar ook voor een goed humeur.', 'textarea', 'Verhaal - alinea 2 (uitgelicht)', 'story', 1),
(UUID(), @wid, 'about', 'about_story_3', 'Of je nu binnenstapt voor een officiële pasfoto, je geluk beproeft met een staatslot of even snel een pakketje wegbrengt: we kennen onze klanten. Geen poespas, maar gewoon goede service en een praatje. Dat is De Bandijk!', 'textarea', 'Verhaal - alinea 3', 'story', 1);
