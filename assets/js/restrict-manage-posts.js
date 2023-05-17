/** global jQuery */
;(function ($) {
  'use strict'

  const restrictManagePosts = {
    selectMetaKey: {},
    selectMetaValue: {}
  }
  /**
   * Initiate.
   */
  restrictManagePosts.init = function () {
    restrictManagePosts.selectMetaKey = $('select[name="_filter_meta_key"]')
    restrictManagePosts.selectMetaValue = $('select[name="_filter_meta_value"]')
    restrictManagePosts.filterClickListener()
    restrictManagePosts.filterChangeListener()
    if (typeof $.fn.select2 !== 'undefined') {
      restrictManagePosts.selectMetaKey.select2()
      restrictManagePosts.selectMetaValue.select2()
    }
  }
  /**
   * Listen for click events on the filter link to trigger auto population of the filter dropdowns.
   */
  restrictManagePosts.filterClickListener = function () {
    $('a[data-select2-ajax]').on('click', function (e) {
      const $this = $(this)
      if (
        $this.data('meta_key') !== 'undefined' &&
        $this.data('meta_value') !== 'undefined' &&
        window.WpUtilities.isNumeric($this.data('meta_value'))
      ) {
        e.preventDefault()
        restrictManagePosts.selectMetaKey.val($this.data('meta_key'))
        restrictManagePosts.selectMetaValue.val($this.data('meta_value'))
        $('#post-query-submit').click()
      }
    })
  }
  /**
   * Listen for change events on the Value selector and apply the optgroup label (meta_key)
   * as the selected value for this.selectMetaKey.
   */
  restrictManagePosts.filterChangeListener = function () {
    restrictManagePosts.selectMetaValue.on(
      'change select2:select',
      function () {
        const $this = $(this.options[this.selectedIndex])
        const optgroup = $this.closest('optgroup')

        if (
          restrictManagePosts.selectMetaKey.val() !== optgroup.prop('label')
        ) {
          restrictManagePosts.selectMetaKey.val(optgroup.prop('label'))
          restrictManagePosts.selectMetaKey.trigger('change.select2')
        }
      }
    )
  }

  $(document).ready(() => restrictManagePosts.init())
}(jQuery))
