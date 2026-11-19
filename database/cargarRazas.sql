-- Archivo de respaldo y carga masiva de Razas (D&D 5e)
USE dyd_wikidot;

-- 1. Inserción de Razas
INSERT INTO razas (name, speed, age, alignment, size, size_description, languages, language_desc) VALUES
('Dragonborn', 30, 'Young dragonborn grow quickly. They walk hours after hatching, attain the size and development of a 10-year-old human child by the age of 3, and reach adulthood by 15. They live to be around 80.', 'Dragonborn tend to extremes, making a conscious choice for one side or the other in the cosmic war between good and evil. Most dragonborn are good, but those who side with evil can be terrible villains.', 'Medium', 'Dragonborn are taller and heavier than humans, standing well over 6 feet tall and averaging almost 250 pounds. Your size is Medium.', 'Common, Draconic', 'You can speak, read, and write Common and Draconic. Draconic is thought to be one of the oldest languages and is often used in the study of magic. The language sounds harsh to most other creatures and includes numerous hard consonants and sibilants.'),
('Dwarf', 25, 'Dwarves mature at the same rate as humans, but they\'re considered young until they reach the age of 50. On average, they live about 350 years.', 'Most dwarves are lawful, believing firmly in the benefits of a well-ordered society. They tend toward good as well, with a strong sense of fair play and a belief that everyone deserves to share in the benefits of a just order.', 'Medium', 'Dwarves stand between 4 and 5 feet tall and average about 150 pounds. Your size is Medium.', 'Common, Dwarvish', 'You can speak, read, and write Common and Dwarvish. Dwarvish is full of hard consonants and guttural sounds, and those characteristics spill over into whatever other language a dwarf might speak.'),
('Elf', 30, 'Although elves reach physical maturity at about the same age as humans, the elven understanding of adulthood goes beyond physical growth to encompass worldly experience. An elf typically claims adulthood and an adult name around the age of 100 and can live to be 750 years old.', 'Elves love freedom, variety, and self-expression, so they lean strongly toward the gentler aspects of chaos. They value and protect others\' freedom as well as their own, and they are more often good than not.', 'Medium', 'Elves range from under 5 to over 6 feet tall and have slender builds. Your size is Medium.', 'Common, Elvish', 'You can speak, read, and write Common and Elvish. Elvish is fluid, with subtle intonations and intricate grammar. Elven literature is rich and varied, and their songs and poems are famous among other races. Many bards learn their language so they can add Elvish ballads to their repertoires.'),
('Gnome', 25, 'Gnomes mature at the same rate humans do, and most are expected to settle down into an adult life by around age 40. They can live 350 to almost 500 years.', 'Gnomes are most often good. Those who tend toward law are sages, engineers, researchers, scholars, investigators, or inventors. Those who tend toward chaos are minstrels, tricksters, wanderers, or fanciful jewelers. Gnomes are good-hearted, and even the tricksters among them are more playful than vicious.', 'Small', 'Gnomes are between 3 and 4 feet tall and average about 40 pounds. Your size is Small.', 'Common, Gnomish', 'You can speak, read, and write Common and Gnomish. The Gnomish language, which uses the Dwarvish script, is renowned for its technical treatises and its catalogs of knowledge about the natural world.'),
('Half-Elf', 30, 'Half-elves mature at the same rate humans do and reach adulthood around the age of 20. They live much longer than humans, however, often exceeding 180 years.', 'Half-elves share the chaotic bent of their elven heritage. They value both personal freedom and creative expression, demonstrating neither love of leaders nor desire for followers. They chafe at rules, resent others\' demands, and sometimes prove unreliable, or at least unpredictable.', 'Medium', 'Half-elves are about the same size as humans, ranging from 5 to 6 feet tall. Your size is Medium.', 'Common, Elvish', 'You can speak, read, and write Common, Elvish, and one extra language of your choice.'),
('Half-Orc', 30, 'Half-orcs mature a little faster than humans, reaching adulthood around age 14. They age noticeably faster and rarely live longer than 75 years.', 'Half-orcs inherit a tendency toward chaos from their orc parents and are not strongly inclined toward good. Half-orcs raised among orcs and willing to live out their lives among them are usually evil.', 'Medium', 'Half-orcs are somewhat larger and bulkier than humans, and they range from 5 to well over 6 feet tall. Your size is Medium.', 'Common, Orc', 'You can speak, read, and write Common and Orc. Orc is a harsh, grating language with hard consonants. It has no script of its own but is written in the Dwarvish script.'),
('Halfling', 25, 'A halfling reaches adulthood at the age of 20 and generally lives into the middle of his or her second century.', 'Most halflings are lawful good. As a rule, they are good-hearted and kind, hate to see others in pain, and have no tolerance for oppression. They are also very orderly and traditional, leaning heavily on the support of their community and the comfort of their old ways.', 'Small', 'Halflings average about 3 feet tall and weigh about 40 pounds. Your size is Small.', 'Common, Halfling', 'You can speak, read, and write Common and Halfling. The Halfling language isn\'t secret, but halflings are loath to share it with others. They write very little, so they don\'t have a rich body of literature. Their oral tradition, however, is very strong. Almost all halflings speak Common to converse with the people in whose lands they dwell or through which they are traveling.'),
('Human', 30, 'Humans reach adulthood in their late teens and live less than a century.', 'Humans tend toward no particular alignment. The best and the worst are found among them.', 'Medium', 'Humans vary widely in height and build, from barely 5 feet to well over 6 feet tall. Regardless of your position in that range, your size is Medium.', 'Common', 'You can speak, read, and write Common and one extra language of your choice. Humans typically learn the languages of other peoples they deal with, including obscure dialects. They are fond of sprinkling their speech with words borrowed from other tongues: Orc curses, Elvish musical expressions, Dwarvish military phrases, and so on.'),
('Tiefling', 30, 'Tieflings mature at the same rate as humans but live a few years longer.', 'Tieflings might not have an innate tendency toward evil, but many of them end up there. Evil or not, an independent nature inclines many tieflings toward a chaotic alignment.', 'Medium', 'Tieflings are about the same size and build as humans. Your size is Medium.', 'Common, Infernal', 'You can speak, read, and write Common and Infernal.')
ON DUPLICATE KEY UPDATE speed=VALUES(speed), age=VALUES(age), alignment=VALUES(alignment), size=VALUES(size), size_description=VALUES(size_description), languages=VALUES(languages), language_desc=VALUES(language_desc);

-- 2. Inserción de Bonos de Características
INSERT INTO raza_bonus (raza_id, ability_score_id, valor) VALUES
((SELECT id FROM razas WHERE name = 'Dragonborn'), (SELECT id FROM ability_scores WHERE index_api = 'str'), 2),
((SELECT id FROM razas WHERE name = 'Dragonborn'), (SELECT id FROM ability_scores WHERE index_api = 'cha'), 1),
((SELECT id FROM razas WHERE name = 'Dwarf'), (SELECT id FROM ability_scores WHERE index_api = 'con'), 2),
((SELECT id FROM razas WHERE name = 'Elf'), (SELECT id FROM ability_scores WHERE index_api = 'dex'), 2),
((SELECT id FROM razas WHERE name = 'Gnome'), (SELECT id FROM ability_scores WHERE index_api = 'int'), 2),
((SELECT id FROM razas WHERE name = 'Half-Elf'), (SELECT id FROM ability_scores WHERE index_api = 'cha'), 2),
((SELECT id FROM razas WHERE name = 'Half-Orc'), (SELECT id FROM ability_scores WHERE index_api = 'str'), 2),
((SELECT id FROM razas WHERE name = 'Half-Orc'), (SELECT id FROM ability_scores WHERE index_api = 'con'), 1),
((SELECT id FROM razas WHERE name = 'Halfling'), (SELECT id FROM ability_scores WHERE index_api = 'dex'), 2),
((SELECT id FROM razas WHERE name = 'Human'), (SELECT id FROM ability_scores WHERE index_api = 'str'), 1),
((SELECT id FROM razas WHERE name = 'Human'), (SELECT id FROM ability_scores WHERE index_api = 'dex'), 1),
((SELECT id FROM razas WHERE name = 'Human'), (SELECT id FROM ability_scores WHERE index_api = 'con'), 1),
((SELECT id FROM razas WHERE name = 'Human'), (SELECT id FROM ability_scores WHERE index_api = 'int'), 1),
((SELECT id FROM razas WHERE name = 'Human'), (SELECT id FROM ability_scores WHERE index_api = 'wis'), 1),
((SELECT id FROM razas WHERE name = 'Human'), (SELECT id FROM ability_scores WHERE index_api = 'cha'), 1),
((SELECT id FROM razas WHERE name = 'Tiefling'), (SELECT id FROM ability_scores WHERE index_api = 'int'), 1),
((SELECT id FROM razas WHERE name = 'Tiefling'), (SELECT id FROM ability_scores WHERE index_api = 'cha'), 2)
ON DUPLICATE KEY UPDATE valor=VALUES(valor);

-- 3. Inserción de Relaciones con Traits
INSERT INTO raza_trait (raza_id, trait_id) VALUES
((SELECT id FROM razas WHERE name = 'Dragonborn'), (SELECT id FROM traits WHERE index_api = 'draconic-ancestry')),
((SELECT id FROM razas WHERE name = 'Dragonborn'), (SELECT id FROM traits WHERE index_api = 'breath-weapon')),
((SELECT id FROM razas WHERE name = 'Dragonborn'), (SELECT id FROM traits WHERE index_api = 'damage-resistance')),
((SELECT id FROM razas WHERE name = 'Dwarf'), (SELECT id FROM traits WHERE index_api = 'darkvision')),
((SELECT id FROM razas WHERE name = 'Dwarf'), (SELECT id FROM traits WHERE index_api = 'dwarven-resilience')),
((SELECT id FROM razas WHERE name = 'Dwarf'), (SELECT id FROM traits WHERE index_api = 'stonecunning')),
((SELECT id FROM razas WHERE name = 'Dwarf'), (SELECT id FROM traits WHERE index_api = 'dwarven-combat-training')),
((SELECT id FROM razas WHERE name = 'Dwarf'), (SELECT id FROM traits WHERE index_api = 'tool-proficiency')),
((SELECT id FROM razas WHERE name = 'Elf'), (SELECT id FROM traits WHERE index_api = 'darkvision')),
((SELECT id FROM razas WHERE name = 'Elf'), (SELECT id FROM traits WHERE index_api = 'fey-ancestry')),
((SELECT id FROM razas WHERE name = 'Elf'), (SELECT id FROM traits WHERE index_api = 'trance')),
((SELECT id FROM razas WHERE name = 'Elf'), (SELECT id FROM traits WHERE index_api = 'keen-senses')),
((SELECT id FROM razas WHERE name = 'Gnome'), (SELECT id FROM traits WHERE index_api = 'darkvision')),
((SELECT id FROM razas WHERE name = 'Gnome'), (SELECT id FROM traits WHERE index_api = 'gnome-cunning')),
((SELECT id FROM razas WHERE name = 'Half-Elf'), (SELECT id FROM traits WHERE index_api = 'darkvision')),
((SELECT id FROM razas WHERE name = 'Half-Elf'), (SELECT id FROM traits WHERE index_api = 'fey-ancestry')),
((SELECT id FROM razas WHERE name = 'Half-Elf'), (SELECT id FROM traits WHERE index_api = 'skill-versatility')),
((SELECT id FROM razas WHERE name = 'Half-Orc'), (SELECT id FROM traits WHERE index_api = 'darkvision')),
((SELECT id FROM razas WHERE name = 'Half-Orc'), (SELECT id FROM traits WHERE index_api = 'savage-attacks')),
((SELECT id FROM razas WHERE name = 'Half-Orc'), (SELECT id FROM traits WHERE index_api = 'relentless-endurance')),
((SELECT id FROM razas WHERE name = 'Half-Orc'), (SELECT id FROM traits WHERE index_api = 'menacing')),
((SELECT id FROM razas WHERE name = 'Halfling'), (SELECT id FROM traits WHERE index_api = 'brave')),
((SELECT id FROM razas WHERE name = 'Halfling'), (SELECT id FROM traits WHERE index_api = 'halfling-nimbleness')),
((SELECT id FROM razas WHERE name = 'Halfling'), (SELECT id FROM traits WHERE index_api = 'lucky')),
((SELECT id FROM razas WHERE name = 'Tiefling'), (SELECT id FROM traits WHERE index_api = 'darkvision')),
((SELECT id FROM razas WHERE name = 'Tiefling'), (SELECT id FROM traits WHERE index_api = 'hellish-resistance')),
((SELECT id FROM razas WHERE name = 'Tiefling'), (SELECT id FROM traits WHERE index_api = 'infernal-legacy'))
ON DUPLICATE KEY UPDATE raza_id=raza_id;
