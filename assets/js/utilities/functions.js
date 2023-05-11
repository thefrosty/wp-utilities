const WpUtilities = {
  /**
   * Is the value numeric?
   * @link https://stackoverflow.com/a/16655847/558561
   * @updated v1.16.0 uses jQuery.isNumeric
   * @param val
   * @returns {boolean}
   */
  isNumeric: (val) => {
    const type = typeof val
    return (type === 'number' || type === 'string') &&
      !isNaN(val - parseFloat(val))
  }
}

window.WpUtilities = WpUtilities
export default WpUtilities
