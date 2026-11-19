-- Archivo de respaldo y carga masiva de Trasfondos (D&D 5e - 2024)
USE dyd_wikidot;

-- 1. Inserción de Trasfondos
INSERT INTO trasfondos (index_api, name, feat_name, equipment_desc) VALUES
('acolyte', 'Acolyte', 'Magic Initiate', 'Choose A or B: (A) Calligrapher\'s Supplies, Book (prayers), Holy Symbol, Parchment (10 sheets), Robe, 8 GP; or (B) 50 GP'),
('criminal', 'Criminal', 'Alert', 'Choose A or B: (A) 2 Daggers, Thieves\' Tools, Crowbar, 2 Pouches, Traveler\'s Clothes, 16 GP; or (B) 50 GP'),
('sage', 'Sage', 'Magic Initiate', 'Choose A or B: (A) Quarterstaff, Calligrapher\'s Supplies, Book (history), Parchment (8 sheets), Robe, 8 GP; or (B) 50 GP'),
('soldier', 'Soldier', 'Savage Attacker', 'Choose A or B: (A) Spear, Shortbow, 20 Arrows, Gaming Set (same as above), Healer\'s Kit, Quiver, Traveler\'s Clothes, 14 GP; or (B) 50 GP')
ON DUPLICATE KEY UPDATE name=VALUES(name), feat_name=VALUES(feat_name), equipment_desc=VALUES(equipment_desc);

-- 2. Inserción de Bonos de Características para Trasfondos
INSERT INTO trasfondo_bonus (trasfondo_id, ability_score_id) VALUES
((SELECT id FROM trasfondos WHERE index_api = 'acolyte'), (SELECT id FROM ability_scores WHERE index_api = 'int')),
((SELECT id FROM trasfondos WHERE index_api = 'acolyte'), (SELECT id FROM ability_scores WHERE index_api = 'wis')),
((SELECT id FROM trasfondos WHERE index_api = 'acolyte'), (SELECT id FROM ability_scores WHERE index_api = 'cha')),
((SELECT id FROM trasfondos WHERE index_api = 'criminal'), (SELECT id FROM ability_scores WHERE index_api = 'dex')),
((SELECT id FROM trasfondos WHERE index_api = 'criminal'), (SELECT id FROM ability_scores WHERE index_api = 'con')),
((SELECT id FROM trasfondos WHERE index_api = 'criminal'), (SELECT id FROM ability_scores WHERE index_api = 'int')),
((SELECT id FROM trasfondos WHERE index_api = 'sage'), (SELECT id FROM ability_scores WHERE index_api = 'con')),
((SELECT id FROM trasfondos WHERE index_api = 'sage'), (SELECT id FROM ability_scores WHERE index_api = 'int')),
((SELECT id FROM trasfondos WHERE index_api = 'sage'), (SELECT id FROM ability_scores WHERE index_api = 'wis')),
((SELECT id FROM trasfondos WHERE index_api = 'soldier'), (SELECT id FROM ability_scores WHERE index_api = 'str')),
((SELECT id FROM trasfondos WHERE index_api = 'soldier'), (SELECT id FROM ability_scores WHERE index_api = 'dex')),
((SELECT id FROM trasfondos WHERE index_api = 'soldier'), (SELECT id FROM ability_scores WHERE index_api = 'con'))
ON DUPLICATE KEY UPDATE trasfondo_id=trasfondo_id;

-- 3. Inserción de Proficiencias para Trasfondos
INSERT INTO trasfondo_proficiencias (trasfondo_id, proficiencia_id) VALUES
((SELECT id FROM trasfondos WHERE index_api = 'acolyte'), (SELECT id FROM proficiencias WHERE nombre = 'Skill: Insight')),
((SELECT id FROM trasfondos WHERE index_api = 'acolyte'), (SELECT id FROM proficiencias WHERE nombre = 'Skill: Religion')),
((SELECT id FROM trasfondos WHERE index_api = 'criminal'), (SELECT id FROM proficiencias WHERE nombre = 'Skill: Sleight of Hand')),
((SELECT id FROM trasfondos WHERE index_api = 'criminal'), (SELECT id FROM proficiencias WHERE nombre = 'Skill: Stealth')),
((SELECT id FROM trasfondos WHERE index_api = 'sage'), (SELECT id FROM proficiencias WHERE nombre = 'Skill: Arcana')),
((SELECT id FROM trasfondos WHERE index_api = 'sage'), (SELECT id FROM proficiencias WHERE nombre = 'Skill: History')),
((SELECT id FROM trasfondos WHERE index_api = 'soldier'), (SELECT id FROM proficiencias WHERE nombre = 'Skill: Athletics')),
((SELECT id FROM trasfondos WHERE index_api = 'soldier'), (SELECT id FROM proficiencias WHERE nombre = 'Skill: Intimidation'))
ON DUPLICATE KEY UPDATE trasfondo_id=trasfondo_id;
