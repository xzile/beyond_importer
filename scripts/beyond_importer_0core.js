const BI_PACKAGE_NAME = "beyond_importer";

/* ------------------------------------------- */
/* BI GLOBALS AND UTILITY FUNCTIONS            */
/* ------------------------------------------- */
const BI = {

    ROOT_DIR: 'workshop/' + BI_PACKAGE_NAME + '/',
    TEMPLATE_DIR: 'workshop/' + BI_PACKAGE_NAME + '/html/',
    CSS_DIR: 'workshop/' + BI_PACKAGE_NAME + '/css/',

    /* ------------------------------------------- */

    init: function () {
        console.log("Beyond Importer | Mod enabled and loaded!");

        hook.add("FTCInit", "BI_INJECT_FTC", function (...args) {

            // Chain the original command
            var ftcRenderActor = _render.FTC_RENDER_ACTOR;
            _render.FTC_RENDER_ACTOR = (function (obj, app, scope) {
                var ret = ftcRenderActor.apply(this, arguments);

                // Inject code here
                BI.createImporter(obj, app, scope);

                return ret;
            });
        });

    },

    createImporter: function (obj, app, scope) {
        setTimeout(function () {
            var charData = obj.data;
            // Attach a button to the character screen
            var clickSync = function (ev) {
                let biId = charData._biId || "";
                ui_prompt({
                    inputs: {
                        'Character ID': biId
                    },
                    click: function (ev, inputs, elem) {
                        BI.getBeyondData(inputs['Character ID'][0]['value'], obj, app);
                        //BI.getBeyondData(4065780);
                    }
                });
            };
            var syncButton = $('<button class="background subtitle alttext">Beyond Import</button>').on('click', clickSync);
            $('[ui-name="ui_characterSheetv2"]').find('button:contains(Attributes)').before(syncButton);
        }, 500);
    },

    getBeyondData: function (characterId, obj, app) {
        let onSuccess = function (jsonData) {
            console.log(jsonData);
            console.log(obj);

            obj.data._biId = characterId;
            if (characterId.length > 0 && jsonData instanceof Object) {
                obj.data.info.name.current = jsonData.name;
                obj.data.info.alignment.current = jsonData.alignment;
                obj.data.info.background.current = jsonData.background;
                obj.data.info.class.current = jsonData.class;
                obj.data.info.race.current = jsonData.race;

                obj.data.attributes.ac.current = jsonData.ac;
                obj.data.attributes.hd.current = jsonData.hd;
                obj.data.attributes.hp.current = jsonData.hp;
                obj.data.attributes.hp.max = jsonData.hpMax;
                obj.data.attributes.proficiency.current = jsonData.proficiency;
                obj.data.attributes.speed.current = jsonData.speed;
                obj.data.attributes.spellcasting.current = jsonData.spellcasting;

                obj.data.abilities.cha.current = jsonData.charisma;
                obj.data.abilities.cha.proficient = (jsonData.proficient_saves.indexOf('Charisma') == -1) ? 0 : 1;
                obj.data.abilities.con.current = jsonData.constitution;
                obj.data.abilities.con.proficient = (jsonData.proficient_saves.indexOf('Constitution') == -1) ? 0 : 1;
                obj.data.abilities.dex.current = jsonData.dexterity;
                obj.data.abilities.dex.proficient = (jsonData.proficient_saves.indexOf('Dexterity') == -1) ? 0 : 1;
                obj.data.abilities.int.current = jsonData.intelligence;
                obj.data.abilities.int.proficient = (jsonData.proficient_saves.indexOf('Intelligence') == -1) ? 0 : 1;
                obj.data.abilities.str.current = jsonData.strength;
                obj.data.abilities.str.proficient = (jsonData.proficient_saves.indexOf('Strength') == -1) ? 0 : 1;
                obj.data.abilities.wis.current = jsonData.wisdom;
                obj.data.abilities.wis.proficient = (jsonData.proficient_saves.indexOf('Wisdom') == -1) ? 0 : 1;

                obj.data.currency.cp.current = jsonData.cp;
                obj.data.currency.pp.current = jsonData.pp;
                obj.data.currency.gp.current = jsonData.gp;
                obj.data.currency.sp.current = jsonData.sp;

                obj.data.experience.lvl = jsonData.level;
                obj.data.experience.level.current = jsonData.level;
                obj.data.experience.exp.current = jsonData.xp;

                for (let skillName in obj.data.skills) {
                    if (jsonData.proficient_skills.indexOf(obj.data.skills[skillName].name) == -1) {
                        obj.data.skills[skillName].current = 0;
                        obj.data.skills[skillName].prof = 0;
                    } else {
                        obj.data.skills[skillName].current = 1;
                        obj.data.skills[skillName].prof = 1;
                    }
                }

                obj.data.traits.languages.current = jsonData.languages;
                obj.data.traits.size.current = jsonData.size;

                // spell book
                let newSpellBook = [];
                jsonData.spells.forEach(function (spell) {
                    let spellTemplate = {
                        "_t": "i",
                        "info": {
                            "name": {
                                "name": "Name",
                                "current": spell.name
                            },
                            "img": {
                                "name": "Artwork"
                            },
                            "notes": {
                                "name": "Description",
                                "current": spell.description
                            }
                        },
                        "source": {
                            "name": "Source",
                            "current": spell.source + ' / ' + spell.source_page
                        },
                        "tags": {
                            "Spell": 1
                        },
                        "_type": "Spell",
                        "quantity": {
                            "name": "Quantity",
                            "min": 0,
                            "current": 1
                        },
                        "price": {
                            "name": "Price",
                            "current": 0
                        },
                        "weight": {
                            "name": "Weight",
                            "min": 0,
                            "current": 0.1
                        },
                        "level": {
                            "name": "Spell Level",
                            "current": spell.level
                        },
                        "type": {
                            "name": "Spell Type",
                            "current": spell.type
                        },
                        "school": {
                            "name": "Spell School",
                            "current": String(spell.school).toLowerCase()
                        },
                        "duration": {
                            "name": "Spell Duration",
                            "current": spell.duration
                        },
                        "time": {
                            "name": "Casting Time",
                            "current": spell.time + ' ' + spell.time_type
                        },
                        "range": {
                            "name": "Spell Range",
                            "current": spell.range
                        },
                        "damage": {
                            "name": "Spell Damage",
                            "current": spell.type_mod,
                            "type": spell.type_sub
                        },
                        "components": {
                            "name": "Spell Components",
                            "current": spell.components
                        },
                        "materials": {
                            "name": "Material Components",
                            "current": spell.materials
                        },
                        "concentration": {
                            "name": "Requires Concentration",
                            "current": (spell.is_concentration) ? 1 : 0
                        },
                        "ritual": {
                            "name": "Ritual Spell",
                            "current": (spell.is_ritual) ? 1 : 0
                        },
                        "modifier": {
                            "name": "Spellcasting Ability"
                        },
                    };

                    spellTemplate.tags[spell.school] = 1;

                    newSpellBook.push(spellTemplate);
                });
                obj.data.spellbook = newSpellBook;
            }

            // inventory
            let newInventory = [];

            function determineOffensiveAbility(data, jsonData, inventoryItem) {
                if (inventoryItem.properties.indexOf('Range')) {
                    return 'dex';
                } else if (inventoryItem.properties.indexOf('Heavy')) {
                    return 'str';
                } else if (inventoryItem.properties.indexOf('Finesse')) {
                    return (jsonData.strength >= jsonData.dexterity) ? 'str' : 'dex';
                }
            }

            jsonData.inventory.forEach(function (inventoryItem) {
                let inventoryTemplate = {
                    "_t": "i",
                    "info": {
                        "name": {
                            "name": "Name",
                            "current": inventoryItem.name
                        },
                        "img": {
                            "name": "Artwork"
                        },
                        "notes": {
                            "name": "Description",
                            "current": inventoryItem.description
                        }
                    },
                    "source": {
                        "name": "Source",
                        "current": inventoryItem.source + ' / ' + inventoryItem.source_page
                    },
                    "tags": {},
                    "_type": inventoryItem.filterType,
                    "quantity": {
                        "name": "Quantity",
                        "min": 0,
                        "current": 1
                    },
                    "price": {
                        "name": "Price",
                        "current": inventoryItem.cost
                    },
                    "weight": {
                        "name": "Weight",
                        "min": 0,
                        "current": inventoryItem.weight
                    },
                    "type": {
                        "name": inventoryItem.filterType + " Type",
                        "current": inventoryItem.type
                    },
                };

                switch (inventoryItem.filterType) {
                    case 'Armor':
                        Object.assign(inventoryTemplate, {
                            "ac": {
                                "name": "Armor Class",
                                "current": inventoryItem.ac,
                            },
                            "strength": {
                                "name": "Required Strength",
                                "current": inventoryItem.strength_requirement,
                            },
                            "stealth": {
                                "name": "Stealth Disadvantage",
                                "current": inventoryItem.stealth_disadvantage,
                            },
                            "proficient": {
                                "name": "Proficient"
                            },
                            "equipped": {
                                "name": "Equipped"
                            },
                        });
                        break;
                    case 'Weapon':
                        Object.assign(inventoryTemplate, {
                            "type": {
                                "name": "Weapon Type",
                                "current": (inventoryItem.category + inventoryItem.attack_type.charAt(0)).toLowerCase()
                            },
                            "hit": {
                                "name": "Attack Bonus",
                            },
                            "damage": {
                                "name": "Damage",
                                "current": inventoryItem.damage,
                                "type": inventoryItem.damage_type.toLowerCase(),
                            },
                            "damage2": {
                                "name": "Alternate Damage",
                                "current": inventoryItem.alt_damage,
                                "type": inventoryItem.damage_type.toLowerCase(),
                            },
                            "range": {
                                "name": "Range",
                                "current": inventoryItem.range + ((inventoryItem.long_range != inventoryItem.range) ? '/' + inventoryItem.long_range : '') + " ft."
                            },
                            "properties": {
                                "name": "Properties",
                                "current": inventoryItem.properties.join(', '),
                            },
                            "proficient": {
                                "name": "Proficient",
                            },
                            "modifier": {
                                "name": "Offensive Ability",
                                "current": determineOffensiveAbility(obj.data, jsonData, inventoryItem)
                            },
                        });
                        break;
                    default:
                        inventoryTemplate["_type"] = "Item";
                        inventoryTemplate.type.name = "Other Type";
                        break;
                }

                newInventory.push(inventoryTemplate);
            });
            obj.data.inventory = newInventory;

            obj.sync("updateAsset");
        };

        let protocol = location.protocol;
        let slashes = protocol.concat("//");
        let host = slashes.concat(location.hostname) + ':30101';

        $.get(
            host + '/api/' + characterId + '/json',
            {},
            onSuccess,
            'json'
        );
    }
};

/* -------------------------------------------- */
/* GM Forge Initialization Hook                 */
/* -------------------------------------------- */

hook.add("Initialize", "BISetup", function (...args) {
    console.log("Initializing Beyond Importer");
    BI.init();
});

