<?php

class AdminAntragFilterForm extends CFormModel
{
    /** @var int */
    public $status = null;
    public $tag    = null;

    /** @var string */
    public $antragstellerIn = null;

    /** @var Antrag [] */
    public $alle_antraege;

    /** @var Aenderungsantrag[] */
    public $alle_aenderungsantraege;

    /** @var Veranstaltung */
    public $veranstaltung;

    /** @var string */
    public $titel = null;

    /**
     * @param Veranstaltung $veranstaltung
     * @param Antrag[] $alle_antraege
     * @param bool $aenderungsantraege
     */
    public function __construct($veranstaltung, $alle_antraege, $aenderungsantraege)
    {
        parent::__construct();
        $this->veranstaltung = $veranstaltung;
        $this->alle_antraege = [];
        $this->alle_aenderungsantraege = [];
        foreach ($alle_antraege as $antrag) {
            if ($antrag->status != Antrag::$STATUS_GELOESCHT) {
                $this->alle_antraege[] = $antrag;
                if ($aenderungsantraege) {
                    foreach ($antrag->aenderungsantraege as $aend) {
                        if ($aend->status != Aenderungsantrag::$STATUS_GELOESCHT) {
                            $this->alle_aenderungsantraege[] = $aend;
                        }
                    }
                }
            }
        }
    }


    /**
     * @return array
     */
    public function rules()
    {
        return array(
            array('status, tag', 'numerical'),
            array('status, tag, titel, antragstellerIn', 'safe'),
        );
    }

    /**
     * @param array $values
     * @param bool $safeOnly
     */
    public function setAttributes($values, $safeOnly = true) {
        parent::setAttributes($values, $safeOnly);
        $this->status = (isset($values["status"]) && $values["status"] != "" ? IntVal($values["status"]) : null);
    }

    /**
     * @return Antrag[]
     */
    public function getFilteredMotions()
    {
        $out = array();
        foreach ($this->alle_antraege as $antrag) {
            $matches = true;

            if ($this->status !== null && $this->status != "" && $antrag->status != $this->status) {
                $matches = false;
            }

            if ($this->tag !== null && $this->tag > 0) {
                $found = false;
                foreach ($antrag->tags as $tag) {
                    if ($tag->id == $this->tag) {
                        $found = true;
                    }
                }
                if (!$found) {
                    $matches = false;
                }
            }

            if ($this->antragstellerIn !== null && $this->antragstellerIn != "") {
                $found = false;
                foreach ($antrag->antragUnterstuetzerInnen as $supp) {
                    if ($supp->rolle == AntragUnterstuetzerInnen::$ROLLE_INITIATORIN && mb_stripos($supp->person->name, $this->antragstellerIn) !== false) {
                        $found = true;
                    }
                }
                if (!$found) {
                    $matches = false;
                }
            }

            if ($this->titel !== null && $this->titel != "" && !mb_stripos($antrag->name, $this->titel)) {
                $matches = false;
            }

            if ($matches) {
                $out[] = $antrag;
            }
        }
        return $out;
    }



    /**
     * @return Aenderungsantrag[]
     */
    public function getFilteredAmendments()
    {
        $out = array();
        foreach ($this->alle_aenderungsantraege as $aend) {
            $matches = true;

            if ($this->status !== null && $this->status != "" && $aend->status != $this->status) {
                $matches = false;
            }

            if ($this->tag !== null && $this->tag > 0) {
                $found = false;
                foreach ($aend->antrag->tags as $tag) {
                    if ($tag->id == $this->tag) {
                        $found = true;
                    }
                }
                if (!$found) {
                    $matches = false;
                }
            }

            if ($this->antragstellerIn !== null && $this->antragstellerIn != "") {
                $found = false;
                foreach ($aend->aenderungsantragUnterstuetzerInnen as $supp) {
                    if ($supp->rolle == AenderungsantragUnterstuetzerInnen::$ROLLE_INITIATORIN && mb_stripos($supp->person->name, $this->antragstellerIn) !== false) {
                        $found = true;
                    }
                }
                if (!$found) {
                    $matches = false;
                }
            }

            if ($this->titel !== null && $this->titel != "" && !mb_stripos($aend->antrag->name, $this->titel)) {
                $matches = false;
            }

            if ($matches) {
                $out[] = $aend;
            }
        }
        return $out;
    }
    /**
     * @return string
     */
    public function getFilterFormFields()
    {
        $str = '';

        $str .= '<label style="float: left; margin-right: 20px;">Status:<br>';
        $str .= '<select name="Search[status]" size="1">';
        $str .= '<option value="">- egal -</option>';
        $statusList = $this->getStatusList();
        foreach ($statusList as $status_id => $status_name) {
            $str .= '<option value="' . $status_id . '" ';
            if ($this->status !== null && $this->status == $status_id) {
                $str .= ' selected';
            }
            $str .= '>' . CHtml::encode($status_name) . '</option>';
        }
        $str .= '</select></label>';

        $tagsList = $this->getTagList();
        if (count($tagsList) > 0) {
            $name = ($this->veranstaltung->tags[0]->istTagesordnungspunkt() ? "Tagesordnungspunkt:" : "Schlagwort:");
            $str .= '<label style="float: left; margin-right: 20px;">' . $name . '<br>';
            $str .= '<select name="Search[tag]" size="1">';
            $str .= '<option value="">- egal -</option>';
            foreach ($tagsList as $tag_id => $tag_name) {
                $str .= '<option value="' . $tag_id . '" ';
                if ($this->tag == $tag_id) {
                    $str .= ' selected';
                }
                $str .= '>' . CHtml::encode($tag_name) . '</option>';
            }
            $str .= '</select></label>';
        }

        $str .= '<label style="float: left; margin-right: 20px;">AntragstellerInnen:<br>';

        $values = [];
        $antragstellerInnenList = $this->getAntragstellerInnenList();
        foreach ($antragstellerInnenList as $antragstellerInName => $antragstellerIn) {
            $values[] = $antragstellerInName;
        }

        $str .= '<div style="display: inline-block;"><input id="antragstellerInSelect" class="typeahead" type="text" placeholder="AntragstellerIn"';
        $str .= 'name="Search[antragstellerIn]" value="' . CHtml::encode($this->antragstellerIn) . '" data-values="' . CHtml::encode(json_encode($values)) . '"></div>';
        $str .= '<script>$(function() {
            var antragstellerInValues = $("#antragstellerInSelect").data("values"),
            matcher = function findMatches(q, cb) {
                var matches, substrRegex;

                // an array that will be populated with substring matches
                matches = [];

                // regex used to determine if a string contains the substring `q`
                substrRegex = new RegExp(q, "i");

                // iterate through the pool of strings and for any string that
                // contains the substring `q`, add it to the `matches` array
                $.each(antragstellerInValues, function(i, str) {
                    if (substrRegex.test(str)) {
                    // the typeahead jQuery plugin expects suggestions to a
                    // JavaScript object, refer to typeahead docs for more info
                    matches.push({ value: str });
                }
                });
                cb(matches);
            };
            $("#antragstellerInSelect").typeahead({
                hint: true,
                highlight: true,
                minLength: 1
            }, {
                name: "antragstellerIn",
                displayKey: "value",
                source: matcher
            });
        });
        </script>';
        $str .= '</label>';

        $str .= '<label style="float: left; margin-right: 20px;">Titel:<br>';
        $str .= '<input type="text" name="Search[titel]" value="' . CHtml::encode($this->titel) . '">';
        $str .= '</label>';

        return $str;
    }

    /**
     * @return array
     */
    public function getStatusList()
    {
        $out = $anz = array();
        foreach ($this->alle_antraege as $antrag) {
            if (!isset($anz[$antrag->status])) {
                $anz[$antrag->status] = 0;
            }
            $anz[$antrag->status]++;
        }
        foreach ($this->alle_aenderungsantraege as $aend) {
            if (!isset($anz[$aend->status])) {
                $anz[$aend->status] = 0;
            }
            $anz[$aend->status]++;
        }
        foreach (Antrag::$STATI as $status_id => $status_name) {
            if (isset($anz[$status_id])) {
                $out[$status_id] = $status_name . " (" . $anz[$status_id] . ")";
            }
        }
        return $out;
    }


    /**
     * @return array
     */
    public function getTagList()
    {
        $tags = $tagsNamen = array();
        foreach ($this->alle_antraege as $antrag) {
            foreach ($antrag->tags as $tag) {
                if (!isset($tags[$tag->id])) {
                    $tags[$tag->id]      = 0;
                    $tagsNamen[$tag->id] = $tag->name;
                }
                $tags[$tag->id]++;
            }
        }
        foreach ($this->alle_aenderungsantraege as $aend) {
            foreach ($aend->antrag->tags as $tag) {
                if (!isset($tags[$tag->id])) {
                    $tags[$tag->id]      = 0;
                    $tagsNamen[$tag->id] = $tag->name;
                }
                $tags[$tag->id]++;
            }
        }
        $out = array();
        foreach ($tags as $tag_id => $anzahl) {
            $out[$tag_id] = $tagsNamen[$tag_id] . " (" . $anzahl . ")";
        }
        asort($out);
        return $out;
    }

    /**
     * @return array
     */
    public function getAntragstellerInnenList()
    {
        $antragstellerInnen = array();
        foreach ($this->alle_antraege as $antrag) {
            foreach ($antrag->antragUnterstuetzerInnen as $supp) {
                if ($supp->rolle != AntragUnterstuetzerInnen::$ROLLE_INITIATORIN) {
                    continue;
                }
                if (!isset($antragstellerInnen[$supp->person->name])) {
                    $antragstellerInnen[$supp->person->name] = 0;
                }
                $antragstellerInnen[$supp->person->name]++;
            }
        }
        foreach ($this->alle_aenderungsantraege as $aend) {
            foreach ($aend->aenderungsantragUnterstuetzerInnen as $supp) {
                if ($supp->rolle != AenderungsantragUnterstuetzerInnen::$ROLLE_INITIATORIN) {
                    continue;
                }
                if (!isset($antragstellerInnen[$supp->person->name])) {
                    $antragstellerInnen[$supp->person->name] = 0;
                }
                $antragstellerInnen[$supp->person->name]++;
            }
        }
        $out = array();
        foreach ($antragstellerInnen as $antragstellerIn => $anzahl) {
            $out[$antragstellerIn] = $antragstellerIn . " (" . $anzahl . ")";
        }
        asort($out);
        return $out;
    }
}
