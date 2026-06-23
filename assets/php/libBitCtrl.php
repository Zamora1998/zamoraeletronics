<?php

/**
 * Class containing functions to check and manipulate specific bit in an integer bit field
 * 
 * @param query_bit() Returns true or false, depending on if the bit is set.
 * @param set_bit() Returns integer with the specific bit set.
 * @param remove_bit() Returns integer with the specific bit unset.
 * @param bool toggle_bit() Returns integer with the specific bit toggled.
 * 
 */
class BitControl {

    /**
     * Returns true or false, depending on if the bit is set.
     *
     * @param int $bitfield  Integer containig various bit switches.
     * @param int $bit Integer bit value to verify.
     *
     * @return bool
     */
    function query_bit($bitfield, $bit) {
        return ( (int) $bitfield & (int) $bit );
    }

    /**
     * Returns integer with the specific bit set.
     *
     * @param int $bitfield  Integer containig various bit switches.
     * @param int $bit Integer bit value to set.
     *
     * @return int
     */
    function set_bit(&$bitfield, $bit) {
        return (int) $bitfield |= (int) $bit;
    }

    /**
     * Returns integer with the specific bit unset.
     *
     * @param int $bitfield Integer containig various bit switches.
     * @param int $bit Integer bit value to unset.
     *
     * @return int
     */
    function remove_bit(&$bitfield, $bit) {
        return (int) $bitfield &= ~(int) $bit;
    }

    /**
     * Returns integer with the specific bit toggled.
     *
     * @param int $bitfield Integer containig various bit switches.
     * @param int $bit Integer bit value to unset.
     *
     * @return int
     */
    function toggle_bit(&$bitfield, $bit) {
        return (int) $bitfield ^= (int) $bit;
    }

}
