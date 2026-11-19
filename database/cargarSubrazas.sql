-- Archivo de respaldo y carga masiva de Subrazas (D&D 5e)
USE dyd_wikidot;

-- 1. Inserción de Subrazas
INSERT INTO subrazas (raza_id, name, speed, age, alignment, size, size_description, languages, language_desc) VALUES
((SELECT id FROM razas WHERE name = 'Elf'), 'High Elf', NULL, '', '', '', '', '', ''),
((SELECT id FROM razas WHERE name = 'Dwarf'), 'Hill Dwarf', NULL, '', '', '', '', '', ''),
((SELECT id FROM razas WHERE name = 'Halfling'), 'Lightfoot Halfling', NULL, '', '', '', '', '', ''),
((SELECT id FROM razas WHERE name = 'Gnome'), 'Rock Gnome', NULL, '', '', '', '', '', '')
ON DUPLICATE KEY UPDATE speed=VALUES(speed), age=VALUES(age), alignment=VALUES(alignment), size=VALUES(size), size_description=VALUES(size_description), languages=VALUES(languages), language_desc=VALUES(language_desc);

-- 2. Inserción de Bonos de Características para Subrazas
INSERT INTO subraza_bonus (subraza_id, ability_score_id, valor) VALUES
((SELECT id FROM subrazas WHERE name = 'High Elf'), (SELECT id FROM ability_scores WHERE index_api = 'int'), 1),
((SELECT id FROM subrazas WHERE name = 'Hill Dwarf'), (SELECT id FROM ability_scores WHERE index_api = 'wis'), 1),
((SELECT id FROM subrazas WHERE name = 'Lightfoot Halfling'), (SELECT id FROM ability_scores WHERE index_api = 'cha'), 1),
((SELECT id FROM subrazas WHERE name = 'Rock Gnome'), (SELECT id FROM ability_scores WHERE index_api = 'con'), 1)
ON DUPLICATE KEY UPDATE valor=VALUES(valor);

-- 3. Inserción de Relaciones de Traits con Subrazas
INSERT INTO subraza_trait (subraza_id, trait_id) VALUES
((SELECT id FROM subrazas WHERE name = 'High Elf'), (SELECT id FROM traits WHERE index_api = 'elf-weapon-training')),
((SELECT id FROM subrazas WHERE name = 'High Elf'), (SELECT id FROM traits WHERE index_api = 'high-elf-cantrip')),
((SELECT id FROM subrazas WHERE name = 'High Elf'), (SELECT id FROM traits WHERE index_api = 'extra-language')),
((SELECT id FROM subrazas WHERE name = 'Hill Dwarf'), (SELECT id FROM traits WHERE index_api = 'dwarven-toughness')),
((SELECT id FROM subrazas WHERE name = 'Lightfoot Halfling'), (SELECT id FROM traits WHERE index_api = 'naturally-stealthy')),
((SELECT id FROM subrazas WHERE name = 'Rock Gnome'), (SELECT id FROM traits WHERE index_api = 'artificers-lore')),
((SELECT id FROM subrazas WHERE name = 'Rock Gnome'), (SELECT id FROM traits WHERE index_api = 'tinker'))
ON DUPLICATE KEY UPDATE subraza_id=subraza_id;
