/**
 * Auto-generated by PhpToTsBundle
 * Do not modify directly!
 */

/**
 * @deprecated because of reasons
 *
 * @template T1 class level generic
 * @template T2 constructor level generic
 * @template U2 property level generic
 * with a newline
 */
declare type GenericTypes<
			T1 extends string,
			T2 extends number,
			U1 extends unknown[],
			T3 extends {
						foo: ("bar" | "baz");
			},
			U2,
			V extends boolean,
			W extends unknown,
> = {
			/**
			 * This is the description for testProperty6.
			 */
			testProperty6: W;
			/**
			 * This is the description for testProperty5
			 */
			testProperty5: {
						foo: (T3 | null);
						bar: U2;
						baz: (V | T3);
			};
			/**
			 * This is the description for testProperty4
			 */
			testProperty4: (T1 | "foo");
			testProperty3: U1;
			testProperty2: T2;
			testProperty1: T2;
}
