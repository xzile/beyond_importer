<?php

namespace App\Services;

class Character
{
    private $data;
    /** @var Config */
    private $config;

    /**
     * Character constructor.
     */
    public function __construct()
    {
        /** @var Config config */
        $this->config = app()->make(Config::class);
    }

    /**
     * @return array
     */
    public function getMethods()
    {
        return get_class_methods(get_class($this));
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param mixed $data
     */
    public function setData($data): void
    {
        $this->data = $data;
    }

    /**
     * @param $name
     * @return mixed|null
     */
    public function __get($name)
    {
        if (method_exists($this, $name)) {
            try {
                return $this->{$name}();
            } catch (\Exception $e) {
                report($e);
                return null;
            }
        } else {
            return null;
        }
    }

    /**
     * @return mixed
     */
    private function name()
    {
        return $this->data->name;
    }

    /**
     * @return mixed
     */
    private function gender()
    {
        return $this->data->gender;
    }

    /**
     * @return mixed
     */
    private function age()
    {
        return $this->data->age;
    }

    /**
     * @return string
     */
    private function alignment()
    {
        if ($this->data->alignmentId == null) {
            // If null, assume true neutral
            return 'tn';
        }
        // Long name
        $alignments = $this->config->getById('alignments', $this->data->alignmentId);
        $name = $alignments->name;
        // Convert to short name
        $shortName = '';
        foreach (explode(' ', strtolower($name)) as $word) {
            $shortName .= mb_substr($word, 0, 1, 'utf-8');
        }
        return $shortName;
    }

    /**
     * @return mixed
     */
    private function race()
    {
        return $this->data->race->fullName;
    }

    /**
     * @return mixed
     */
    private function size()
    {
        return $this->data->race->size;
    }

    /**
     * @return null
     */
    private function background()
    {
        try {
            return $this->data->background->definition->name;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * @return mixed
     */
    private function strength()
    {
        return $this->getStat('STR');
    }

    /**
     * @param string $id
     * @return mixed
     */
    public function getStat(string $id)
    {
        $id = $this->getStatIdByKey($id);
        $stats = $this->getById('stats', $id);
        $statValue = $stats->value;

        // Get modifiers
        foreach ($this->data->modifiers as $type => $modifier) {
            foreach ($modifier as $mod) {
                if ($mod->type == 'bonus' && $mod->entityId == $id) {
                    $statValue += $mod->value;
                }
            }
        }

        return $statValue;
    }

    /**
     * @param string $id
     * @return mixed|string
     */
    public function getStatIdByKey(string $id)
    {
        $configStats = $this->config->getById('stats', strtoupper($id), 'key');
        $id = $configStats->id;
        return $id;
    }

    /**
     * @param $attr
     * @param $id
     * @param string $key
     * @return \Illuminate\Support\Collection
     */
    public function getById($attr, $id, $key = 'id')
    {
        return collect($this->data->{$attr})->firstWhere($key, $id);
    }

    /**
     * @return mixed
     */
    private function dexterity()
    {
        return $this->getStat('DEX');
    }

    /**
     * @return mixed
     */
    private function constitution()
    {
        return $this->getStat('CON');
    }

    /**
     * @return mixed
     */
    private function intelligence()
    {
        return $this->getStat('INT');
    }

    /**
     * @return mixed
     */
    private function wisdom()
    {
        return $this->getStat('WIS');
    }

    /**
     * @return mixed
     */
    private function charisma()
    {
        return $this->getStat('CHA');
    }

    /**
     * @return int
     */
    private function speed()
    {
        return $this->data->race->weightSpeeds->normal->walk ?? 30;
    }

    /**
     * @return mixed
     */
    private function cp()
    {
        return $this->data->currencies->cp;
    }

    /**
     * @return mixed
     */
    private function sp()
    {
        return $this->data->currencies->sp;
    }

    /**
     * @return mixed
     */
    private function gp()
    {
        return $this->data->currencies->gp;
    }

    /**
     * @return mixed
     */
    private function ep()
    {
        return $this->data->currencies->ep;
    }

    /**
     * @return mixed
     */
    private function pp()
    {
        return $this->data->currencies->pp;
    }

    /**
     * @return float|int|null
     */
    private function ac()
    {
        // Base AC is 10
        $baseAc = 10;
        $canAddDex = true;
        $dexMaxAdd = 10;
        $isArmored = false;
        $unarmoredBonus = 0;

        // Search inventory for equipped armor
        $equipmentAc = null;
        foreach ($this->data->inventory as $item) {
            if ($item->definition->filterType == 'Armor' && $item->equipped == true) {
                $equipmentAc += $item->definition->armorClass;
                $isArmored = true;

                if ($item->definition->type == "Heavy Armor") {
                    $canAddDex = false;
                } elseif ($item->definition->type == 'Medium Armor') {
                    $dexMaxAdd = 2;
                }
            }
            if ($item->definition->filterType == "Wondrous Item" && $item->equipped == true) {
                foreach ($item->definition->grantedModifiers as $modifier) {
                    if ($modifier->subType == 'unarmored-armor-class') {
                        $unarmoredBonus += $modifier->value;
                    }
                }
            }
        }

        $ac = $equipmentAc ?? $baseAc;

        // Add DEX modifier
        if ($canAddDex == true) {
            $ac += min($this->getStatMod('DEX'), $dexMaxAdd);
        }
        if (!$isArmored) {
            $ac += $unarmoredBonus;
        }

        return $ac;
    }

    /**
     * @param string $id
     * @return float
     */
    public function getStatMod(string $id)
    {
        $stat = $this->getStat($id);
        $mod = floor(($stat - 10) / 2);

        return $mod;
    }

    /**
     * @return float|int
     */
    private function proficiency()
    {
        $level = $this->level();

        return ceil($level / 4) + 1;
    }

    /**
     * @return int
     */
    private function level()
    {
        $level = 0;
        foreach ($this->data->classes as $class) {
            $level += $class->level;
        }
        return $level;
    }

    /**
     * @return mixed
     */
    private function class()
    {
        $classes = [];
        foreach ($this->data->classes as $class) {
            $classes[$class->definition->name] = $class->level;
        }
        return str_replace('=', ' ', http_build_query($classes, '', ' / '));
    }

    /**
     * @return string
     */
    private function spellcasting()
    {
        $spellCastingId = null;
        foreach ($this->data->classes as $class) {
            if ($spellCastingId === null && !is_null($class->definition->spellCastingAbilityId)) {
                $spellCastingId = $class->definition->spellCastingAbilityId;
            }
        }

        if (is_null($spellCastingId)) {
            // Default to INT
            $spellCastingId = $this->getStatIdByKey('INT');
        }

        return strtolower($this->getStatKeyById($spellCastingId));
    }

    /**
     * @param string $id
     * @return mixed|string
     */
    public function getStatKeyById(string $id)
    {
        $configStats = $this->config->getById('stats', $id, 'id');
        $id = $configStats->key;
        return $id;
    }

    /**
     * @return string
     */
    private function languages()
    {
        $languages = [
        ];

        // Get modifiers
        foreach ($this->data->modifiers as $type => $modifier) {
            foreach ($modifier as $mod) {
                if ($mod->type == 'language') {
                    $languages[$mod->friendlySubtypeName] = $mod->friendlySubtypeName;
                }
            }
        }

        if (empty($languages)) {
            // Give Common by default
            $languages['Common'] = 'Common';
        }

        return implode(', ', $languages);
    }

    /**
     * @return array
     */
    private function proficient_saves()
    {
        $saves = [];

        foreach ($this->data->modifiers as $type => $modifier) {
            foreach ($modifier as $mod) {
                if ($mod->type == 'proficiency' && strpos($mod->subType, 'saving-throws') !== false) {
                    list($stat) = explode('-', $mod->subType);
                    $saves[] = ucwords($stat);
                }
            }
        }

        return $saves;
    }

    /**
     * @return array
     */
    private function proficient_skills()
    {
        // Get skills in the config
        $allSkills = [];
        foreach ($this->config->data->abilitySkills as $abilitySkill) {
            $allSkills[$abilitySkill->name] = $abilitySkill->name;
        }

        // Get modifiers
        $skills = [];
        foreach ($this->data->modifiers as $type => $modifier) {
            foreach ($modifier as $mod) {
                if ($mod->type == 'proficiency' && in_array($mod->friendlySubtypeName, $allSkills)) {
                    $skills[$mod->friendlySubtypeName] = $mod->friendlySubtypeName;
                }
            }
        }

        return array_values($skills);
    }

    /**
     * @return int
     */
    private function hd()
    {
        $level = $this->level();

        // Look for used dice
        $diceUsed = 0;
        foreach ($this->data->classes as $class) {
            $diceUsed += $class->hitDiceUsed;
        }

        return ($level - $diceUsed);
    }

    /**
     * @return float|int
     */
    private function hp()
    {
        $maxHp = $this->hpMax();
        $damageTaken = $this->data->removedHitPoints ?? 0;
        $tempHp = $this->data->temporaryHitPoints ?? 0;
        return $maxHp - $damageTaken + $tempHp;
    }

    /**
     * @return float|int
     */
    private function hpMax()
    {
        $maxHp = 0;

        $maxHp += $this->data->baseHitPoints;

        // add constitution modifier for each level
        $conMod = $this->getStatMod('CON');
        $level = $this->level();
        $maxHp += ($level * $conMod);

        return $maxHp;
    }

    /**
     * @return int
     */
    private function xp()
    {
        return $this->data->currentXp ?? 0;
    }

    /**
     * @return array
     */
    private function spells()
    {
        $spells = [];

        // Get all spell definitions
        $spellDefinitions = [];
        // classSpells
        foreach ($this->data->classSpells as $classSpell) {
            foreach ($classSpell->spells as $spell) {
                $spellDefinitions[] = $spell;
            }
        }
        // other spells
        foreach (['race', 'class', 'item', 'feat'] as $subType) {
            $spellDefinitions = array_merge($spellDefinitions, $this->data->spells->{$subType});
        }

        // Parse definitions
        foreach ($spellDefinitions as $spell) {
            if ($spell->prepared == true || $spell->definition->level == 0) {
                $spellData = [
                    'name' => $spell->definition->name,
                    'description' => "{$spell->definition->description}",
                    'description_clean' => strip_tags("{$spell->definition->description}"),
                    'level' => $spell->definition->level,
                    'school' => $spell->definition->school,
                    'duration' => $spell->definition->duration->durationType,
                    'time' => $spell->definition->activation->activationTime,
                    'time_type' => $this->config->getById('activationTypes', $spell->definition->activation->activationType)->name,
                    'range' => $spell->definition->range->rangeValue,
                    'is_concentration' => $spell->definition->concentration,
                    'is_ritual' => $spell->definition->ritual,
                    'source' => $this->config->getById('sources', $spell->definition->sourceId)->name,
                    'source_page' => $spell->definition->sourcePageNumber,
                ];

                // components
                $components = [];
                foreach ($spell->definition->components as $component) {
                    $components[] = $this->config->getById('spellComponents', $component)->shortName;
                }
                $spellData['components'] = implode(', ', $components);
                $spellData['materials'] = $spell->definition->componentsDescription;

                // Spell types
                if ($spell->definition->requiresSavingThrow) {
                    // Saving throws
                    $spellData['type'] = 'save';
                    $spellData['type_mod'] = $spell->definition->modifiers[0]->die->diceString;
                    $spellData['type_sub'] = $spell->definition->modifiers[0]->subType;
                    $spellData['type_other'] = $this->getStatKeyById($spell->definition->saveDcAbilityId);
                } elseif ($spell->definition->requiresAttackRoll) {
                    // Attack rolls
                    $spellData['type'] = 'attack';
                    $spellData['type_mod'] = $spell->definition->modifiers[0]->die->diceString;
                    $spellData['type_sub'] = $spell->definition->modifiers[0]->subType;

                } elseif (strlen($spell->definition->healing)) {
                    // Healing
                    $spellData['type'] = 'heal';
                    $spellData['type_mod'] = $spell->definition->healing;
                    $spellData['type_sub'] = 'healing';
                }

                $spells[] = $spellData;
            }
        }

        return $spells;
    }

    /**
     * @return array
     */
    private function inventory()
    {
        $inventory = [];

        foreach ($this->data->inventory as $item) {
            $newItem = [
                'name' => $item->definition->name,
                'description' => "{$item->definition->description}",
                'description_clean' => strip_tags("{$item->definition->description}"),
                'type' => $item->definition->type,
                'filterType' => $item->definition->filterType,
                'weight' => $item->definition->weight,
                'cost' => $item->definition->cost,
                'quantity' => $item->quantity,
                'source' => ($item->definition->sourceId !== null) ? $this->config->getById('sources', $item->definition->sourceId)->name : '',
                'source_page' => $item->definition->sourcePageNumber ?? '',
            ];

            switch ($item->definition->filterType) {
                case 'Armor':
                    $newItem['ac'] = $item->definition->armorClass;
                    $newItem['stealth_disadvantage'] = ($item->definition->stealthCheck == 2);
                    $newItem['strength_requirement'] = $item->definition->strengthRequirement;
                    break;
                case 'Weapon':
                    $newItem['damage'] = $item->definition->damage->diceString;
                    $newItem['damage_type'] = $item->definition->damageType;
                    $newItem['range'] = $item->definition->range;
                    $newItem['long_range'] = $item->definition->longRange;

                    $newItem['properties'] = [];
                    foreach ($item->definition->properties as $property) {
                        $newItem['properties'][] = $property->name;

                        switch ($property->name) {
                            case 'Versatile':
                                $newItem['alt_damage'] = $property->notes;
                                break;
                        }
                    }

                    $newItem['category'] = $this->config->getById('weaponCategories', $item->definition->categoryId)->name;
                    $newItem['attack_type'] = $this->config->getById('rangeTypes', $item->definition->attackType)->name;
                    break;
            }

            $inventory[] = $newItem;
        }

        return $inventory;
    }
}
