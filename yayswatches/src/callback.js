yay_swatch_frontend_init = function (yaySwatches, is_ajax = false) {
  jQuery(".variations_form:not(.yay-swatch-init)").each(function (
    index,
    element
  ) {
    var _form = jQuery(this);
    if (is_ajax) {
      _form.wc_variation_form();
    }
    _form.addClass("yay-swatch-init");
    const yay_product = _form.yay_variation_form();
    if ("yes" === yaySwatches.is_product_page) {
      window.yay_product = yay_product;
    }
    // add tooltip
    const yay_variant_wrapper = _form.find(".yay-variant-wrapper");
    if (yay_variant_wrapper.length) {
      yay_variant_wrapper.each(function () {
        if ("yes" === jQuery(this).data("show-tooltip")) {
          const tooltip_options = {
            arrow: jQuery(this).data("arrow"),
            shadow: jQuery(this).data("shadow"),
            animation: jQuery(this).data("animation"),
            showImage: jQuery(this).data("show-image") === "yes" ? "yes" : "no",
          };
          // Add tooltip to swatches
          const tippySelector =
            ".yay-swatches-attribute-term[data-type='swatch'], .yay-swatches-attribute-term[data-type='variant_image']";
          tippy(tippySelector, {
            animation:
              "yes" === tooltip_options.animation ? "shift-toward" : "fade",
            animateFill: false,
            duration: 250,
            arrowType: "sharp",
            arrow: "yes" === tooltip_options.arrow ? true : false,
            theme: "yes" === tooltip_options.shadow ? "shadow" : "",
            allowHTML: tooltip_options.showImage === "yes" ? true : false,
            content(reference) {
              const text = reference.getAttribute("data-tippy-text") || "";
              const img = reference.getAttribute("data-tooltip-img");
              if (tooltip_options.showImage === "yes") {
                if (img) {
                  const wrapper = document.createElement("div");
                  wrapper.style.textAlign = "center";
                  wrapper.innerHTML = `
                    <img src="${img}" style="display:block; margin:0 auto 6px; max-width:150px; height:auto;" /> 
                    <span>${text}</span>
                  `;
                  return wrapper;
                }
                return text;
              }
              return text;
            },
          });
        }
      });
    }
  });
};

yay_swatch_frontend_start = function (yaySwatches) {
  yay_swatch_frontend_init(yaySwatches);
  // MutationObserver for DOM changes
  const observer = new MutationObserver((mutationsList) => {
    mutationsList.forEach((mutation) => {
      if (mutation.type === "childList") {
        mutation.addedNodes.forEach((node) => {
          if (node.nodeType === 1) {
            if (jQuery(".variations_form:not(.yay-swatch-init)").length) {
              yay_swatch_frontend_init(yaySwatches, true);
            }
          }
        });
      }
    });
  });
  observer.observe(document.body, { childList: true, subtree: true });
};

yay_swatch_archive_change_image = function (form, variation) {
  if (
    variation &&
    variation.image &&
    variation.image.src &&
    variation.image.thumb_src &&
    variation.image.thumb_src.length > 1
  ) {
    // Click variant end
    form._imageWrapper.wc_set_variation_attr("alt", variation.image.alt);
    form._imageWrapper.wc_set_variation_attr(
      "sizes",
      variation.image.thumb_sizes
    );
    form._imageWrapper.wc_set_variation_attr("src", variation.image.src);
    form._imageWrapper.wc_set_variation_attr(
      "height",
      variation.image.thumb_src_h
    );
    form._imageWrapper.wc_set_variation_attr(
      "width",
      variation.image.thumb_src_w
    );
    form._imageWrapper.wc_set_variation_attr("srcset", variation.image.srcset);
    form._imageWrapper.wc_set_variation_attr("title", variation.image.title);
  }
};

yay_swatch_is_soldout_hide_interact = function (yaySwatches) {
  const soldOutShowHide = yaySwatches.sold_out.soldOutShowHideOptions;
  const hideStyle = yaySwatches.sold_out.soldOutHideStyle;
  return "hide" === soldOutShowHide && "interactive" === hideStyle;
};

yay_swatch_get_field_by_term_selected = function (
  form,
  dataset,
  attribute_fields,
  yaySwatches
) {
  var field = attribute_fields.filter(
    (index, attribute) => attribute.id === dataset.attribute
  );
  if (yaySwatches.wc_product_bundles_active) {
    form.find(".reset_bundled_variations_fixed").show();
    field = attribute_fields.filter(
      (index, attribute) =>
        jQuery(attribute).data("attribute_name") ===
        "attribute_" + dataset.attribute
    );
  }
  return field;
};

yay_swatch_get_key_by_term_selected = function (event, yaySwatches) {
  var get_attribute_name = jQuery(event.target).data("attribute_name");
  get_attribute_name = get_attribute_name.split("attribute_");
  var key_selected =
    undefined != get_attribute_name[1]
      ? get_attribute_name[1]
      : event.target.id;
  return key_selected;
};

yay_swatch_get_label_by_term_selected = function (
  form,
  current_attributes,
  getLableName,
  yaySwatches
) {
  for (const [key, value] of Object.entries(current_attributes)) {
    var form_element = form.find(
      'select[data-attribute_name="attribute_' + key + '"]'
    );
    if (!form_element) {
      form_element = form.find("#" + key);
    }
    const text = form_element.find(`option[value="${value}"]`).text();
    getLableName(form_element, { text: text });
  }
};

// Compatibles

yay_swatch_product_change_image_compatibles = function (theme, variation) {
  if ("oxygen" === theme) {
    var img_selector =
      ".yay-swatches-product-details-wrapper .st-product-container .st-product-image img";
    jQuery(img_selector).attr("src", variation.image.url);
  }
};

yay_swatch_product_hide_clear_button_compatibles = function (
  form,
  yaySwatches
) {
  if (yaySwatches.wc_product_bundles_active) {
    form.find(".reset_bundled_variations_fixed").hide();
  }
};

// YAY SWATCHES COMPATIBLES
yay_swatch_compatibles = function () {
  yay_swatch_wc_composite_products_compatibles();
};

// WooCommerce Product Bundles
yay_swatch_product_bundle_compatibles = function (yaySwatches) {
  if (jQuery(".bundle_form .bundle_data").length > 0) {
    yay_swatch_frontend_init(yaySwatches);
  }
  jQuery(document.body).on(
    "click",
    "input.bundled_product_checkbox",
    function (event) {
      var _input = jQuery(this),
        is_checked = _input.is(":checked"),
        _content = _input
          .closest(".details")
          .find(".bundled_item_cart_content.variations_form.yay-swatch-init");
      if (is_checked) {
        _content.addClass("yay-swatch-clicked");
      } else {
        _content.removeClass("yay-swatch-clicked");
      }
    }
  );
};

// WooCommerce Composite Products plugin
yay_swatch_wc_composite_products_compatibles = function () {
  if (window.yaySwatches.wc_composite_products_active) {
    jQuery(document.body).on(
      "wc-composite-initializing",
      function (event, composite) {
        if (typeof jQuery.fn.yay_variation_form === "function") {
          composite.actions.add_action(
            "component_scripts_initialized",
            function (step) {
              if ("variable" === step.get_selected_product_type()) {
                step.$component_summary_content.yay_variation_form();
              }
            },
            10,
            this
          );
        }
      }
    );
  }
};

// Hide Clear button
jQuery(function ($) {
  $(".variations_form").each(function () {
    const _form = $(this);
    const yay_variant_wrapper = _form.find(".yay-variant-wrapper");

    if (yay_variant_wrapper.length) {
      const clearButton = yay_variant_wrapper.data("clear-button");

      const resetLink = _form.find(".reset_variations").filter(function () {
        return !$(this).closest(".yay-swatch-reset-variations").length;
      });

      if (clearButton !== "enable") {
        resetLink.remove();
      }
    }
  });
});

// Get all attribute defaults from selects
jQuery(function ($) {
  yay_swatch_get_all_products_attributes = function ($wrapper) {
    const allAttr = {};

    $wrapper.find("select[data-attribute_name]").each(function () {
      const $select = $(this);
      const attrName = $select.data("attribute_name");

      const values = [];
      $select.find("option").each(function () {
        const val = $(this).val();
        if (val) values.push(val);
      });

      allAttr[attrName] = values;
    });

    return allAttr;
  };
});

function parseColorToRgb(colorString) {
  const backgroundOnly = colorString.split(";")[0].trim();

  const hexMatches = backgroundOnly.match(/#([0-9a-f]{3,6})/gi);
  if (hexMatches && hexMatches.length) {
    return hexMatches.map((hex) => {
      hex = hex.replace(/^#/, "");
      if (hex.length === 3) {
        hex = hex
          .split("")
          .map((c) => c + c)
          .join("");
      }
      const num = parseInt(hex, 16);
      return {
        r: (num >> 16) & 255,
        g: (num >> 8) & 255,
        b: num & 255,
      };
    });
  }

  const rgbMatches = backgroundOnly.match(
    /rgb\(\s*(\d+),\s*(\d+),\s*(\d+)\s*\)/gi
  );
  if (rgbMatches && rgbMatches.length) {
    return rgbMatches.map((rgb) => {
      const parts = rgb.match(/rgb\(\s*(\d+),\s*(\d+),\s*(\d+)\s*\)/i);
      return {
        r: parseInt(parts[1], 10),
        g: parseInt(parts[2], 10),
        b: parseInt(parts[3], 10),
      };
    });
  }

  return null;
}

function getLuminance({ r, g, b }) {
  const srgb = [r, g, b].map((v) => {
    v /= 255;
    return v <= 0.03928 ? v / 12.92 : Math.pow((v + 0.055) / 1.055, 2.4);
  });
  return 0.2126 * srgb[0] + 0.7152 * srgb[1] + 0.0722 * srgb[2];
}
// Get Color Tick Selected
function getTickColor(backgroundStyle) {
  if (backgroundStyle.includes("url(")) {
    return "#000";
  }

  if (backgroundStyle.includes("linear-gradient")) {
    const rgbs = parseColorToRgb(backgroundStyle);
    if (rgbs && rgbs.length >= 2) {
      const avg = {
        r: Math.round((rgbs[0].r + rgbs[1].r) / 2),
        g: Math.round((rgbs[0].g + rgbs[1].g) / 2),
        b: Math.round((rgbs[0].b + rgbs[1].b) / 2),
      };
      const luminance = getLuminance(avg);
      return luminance > 0.5 ? "#000" : "#fff";
    }
  }

  const rgbs = parseColorToRgb(backgroundStyle);

  if (rgbs && rgbs.length) {
    const luminance = getLuminance(rgbs[0]);
    return luminance > 0.5 ? "#000" : "#fff";
  }

  return "#000";
}
