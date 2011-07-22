<?php
/*
    This file is part of Erebot.

    Erebot is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    Erebot is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with Erebot.  If not, see <http://www.gnu.org/licenses/>.
*/

class Erebot_Module_Countdown_Game
{
    protected $numbers;
    protected $target;

    protected $bestProposal;

    protected $allowedNumbers = array(
        1, 2,  3,  4,  5,  6,   7,
        8, 9, 10, 25, 50, 75, 100,
    );

    protected $minTarget;
    protected $maxTarget;

    public function __construct($minTarget = 100, $maxTarget = 999, $nbNumbers = 7, $allowedNumbers = NULL)
    {
        /// @TODO: refactor checks to avoid redundancy.
        if (!is_int($minTarget))
            throw new Erebot_Module_Countdown_InvalidValue(
                '$minTarget',
                'integer',
                typeof($minTarget)
            );
        if ($minTarget < 100)
            throw new Erebot_Module_Countdown_InvalidValue(
                '$minTarget',
                'number >= 100',
                $minTarget
            );
        $this->minTarget = $minTarget;

        if (!is_int($maxTarget))
            throw new Erebot_Module_Countdown_InvalidValue(
                '$maxTarget',
                'integer',
                typeof($maxTarget)
            );
        if ($maxTarget <= $this->minTarget)
            throw new Erebot_Module_Countdown_InvalidValue(
                '$maxTarget',
                'number > minTarget',
                $maxTarget
            );
        $this->maxTarget = $maxTarget;

        if (!is_int($nbNumbers))
            throw new Erebot_Module_Countdown_InvalidValue(
                '$nbNumbers',
                'integer',
                typeof($nbNumbers)
            );
        if ($nbNumbers < 1)
            throw new Erebot_Module_Countdown_InvalidValue(
                '$nbNumbers',
                'number > 1',
                $nbNumbers
            );

        if ($allowedNumbers !== NULL) {
            if (!is_array($allowedNumbers))
                throw new Erebot_Module_Countdown_InvalidValue(
                    '$allowedNumbers',
                    'array',
                    typeof($allowedNumbers)
                );
            if (!count($allowedNumbers))
                throw new Erebot_Module_Countdown_InvalidValue(
                    '$allowedNumbers',
                    'non-empty array',
                    'empty array'
                );
            foreach ($allowedNumbers as $allowedNumber) {
                if (!is_int($allowedNumber))
                    throw new Erebot_Module_Countdown_InvalidValue(
                        '$allowedNumbers',
                        'array of int',
                        'array of '.typeof($allowedNumber)
                    );
                if ($allowedNumber < 1)
                    throw new Erebot_Module_Countdown_InvalidValue(
                        '$allowedNumbers',
                        'array of int >= 1',
                        $allowedNumber
                    );
            }
            $this->allowedNumbers = $allowedNumbers;
        }
        $this->bestProposal = NULL;
        $this->_chooseNumbers();
    }

    protected function _chooseNumbers()
    {
        $this->numbers = array();
        for ($i = 0; $i < $nbNumbers; $i++) {
            $key = array_rand($this->allowedNumbers);
            $this->numbers[] = $this->allowedNumbers[$key];
        }

        $this->target       = mt_rand($this->minTarget, $this->maxTarget);
    }

    public function __destruct()
    {
        unset($this->bestProposal);
    }

    public function getNumbers()
    {
        return $this->numbers;
    }

    public function getTarget()
    {
        return $this->target;
    }

    public function & getBestProposal()
    {
        return $this->bestProposal;
    }

    /// @TODO: write an interface for formulae and use it there.
    public function proposeFormula(Erebot_Module_Countdown_Formula &$formula)
    {
        $gameNumbers    = $this->numbers;
        $formulaNumbers = $formula->getNumbers();

        foreach ($formulaNumbers as $number) {
            $key = array_search($number, $gameNumbers);
            if ($key === FALSE)
                throw new Erebot_Module_Countdown_UnavailableNumberException();
            unset($gameNumbers[$key]);
        }

        if ($this->bestProposal === NULL) {
            $this->bestProposal =&  $formula;
            return TRUE;
        }

        $oldDst = abs($this->bestProposal->getResult() - $this->target);
        $newDst = abs($formula->getResult() - $this->target);
        if ($newDst < $oldDst) {
            $this->bestProposal =&  $formula;
            return TRUE;
        }

        return FALSE;
    }
}

