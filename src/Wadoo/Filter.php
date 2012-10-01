<?php

	namespace Wadoo;

	/**
	 * Generic Filter interface.
	 * Must be implemented by all filters.
	 */
	interface Filter
	{
		/**
		 * Called when the application is bootstrapped.
		 * Should return an array of events or a single string with the event name.
		 * @return array|string
		 */
		public function register();

		/**
		 * Called when an event is fired.
		 * @param $context an array of variable data depending on the event
		 * @return $context return the same/updated $context
		 */
		public function fire($context);
	}
