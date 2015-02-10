<?php
 class Null_Positioner extends Positioner { function __construct(Frame_Decorator $frame) { parent::__construct($frame); } function position() { return; } } 