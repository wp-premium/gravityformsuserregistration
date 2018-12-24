<?php

/**
 * Allows values to be injected into filters and actions.
 *
 * @since 3.9.4
 *
 * Class GF_User_Registration_Dynamic_Hook
 */
class GF_User_Registration_Dynamic_Hook {
	/**
	 * @since 3.9.4
	 *
	 * @var mixed
	 */
	private $values;

	/**
	 * @since 3.9.4
	 *
	 * @var mixed
	 */
	private $class = null;

	/**
	 * Stores the values for later use.
	 *
	 * @since 3.9.4
	 *
	 * @param mixed $values
	 * @param null  $class
	 */
	public function __construct( $values, $class = null ) {

		$this->values = $values;

		if ( $class ) {
			$this->class = $class;
		}
	}

	/**
	 * Runs the hook callback function.
	 *
	 * @since 3.9.4
	 *
	 * @param  string $callback    The name of the method.
	 * @param  array  $filter_args The args passed by the filter.
	 *
	 * @return mixed
	 */
	public function __call( $callback, $filter_args ) {

		$args = array( $filter_args, $this->values );

		if ( $this->class ) {
			if ( is_callable( array( $this->class, $callback ) ) ) {
				return call_user_func_array( array( $this->class, $callback ), $args );
			}
		}
		if ( is_callable( $callback ) ) {
			return call_user_func_array( $callback, $args );
		}
	}
}
