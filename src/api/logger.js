/* eslint no-console: 0 */

export default {
  /**
   * Called when API call catches exception,
   * good place for custom reporting etc.
   *
   * @since 0.2.0
   * @param response
   */
  error(response) {
    console.log(response);
  },
};
