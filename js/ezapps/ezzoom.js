var conversionRate = 1.0;

function convertFromJson(json) {

                        var re = new RegExp("[^0-9\\" + json.format.decimalSymbol + "]","g");

                        $$('span.price').each(function(item) {
                        	item.innerHTML = formatCurrency(item.innerHTML.replace(re, '') * json.conversionRate, json.format );
                        });

                        conversionRate = json.conversionRate;

			if(typeof optionsPrice!='undefined') {

				optionsPrice.priceTemplate = new Template(json.userTemplate);
                                optionsPrice.priceFormat   = json.format;
                                optionsPrice.formatPrice = function (price) {
                                        return formatCurrency(price * conversionRate, this.priceFormat);
                                }

			}

			if(typeof spConfig!= 'undefined') {

                                spConfig.priceTemplate = new Template(json.userTemplate);

                                spConfig.formatPrice = function (price, showSign) {
                                       var str = '';
                                       price = parseFloat(price) * conversionRate;
                                       if (showSign) {
                                                if (price < 0) {
                                                        str += '-';
                                                        price = -price;
                                                    } else {
                                                        str += '+';
                                                    }
                                        }
                                        var roundedPrice = (Math.round(price * 100) / 100).toString();
                                        if (this.prices && this.prices[roundedPrice]) {
                                                str += this.prices[roundedPrice];
                                       } else {
                                                str += this.priceTemplate.evaluate({
                                                price: price.toFixed(2)
                                            });
                                        }
                                        return str;
                                }

	                        $$('.super-attribute-select').each(function (element)  {
        	                       spConfig.configureElement(element);
                	        });
			}


			if (typeof checkout != 'undefined') {

				checkout.gotoSection = function(section)
				{
				        section = $('opc-'+section);
				        section.addClassName('allow');
				        this.accordion.openSection(section);

					var re = new RegExp("[^0-9\\" + json.format.decimalSymbol + "]","g");
		                        $$('span.price').each(function(item) {
                		                item.innerHTML = formatCurrency(item.innerHTML.replace(re, '') * json.conversionRate, json.format );
		                        });

    				}
			}

}
