<?php

function wc_phorest_settings( $option, $section, $default = '' ){

	$options = get_option( $section, [] );

	return isset( $options[ $option ] ) ? $options[ $option ] : $default;
}