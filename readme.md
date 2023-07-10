# WooCommerce Attribute Standardizer Plugin

## Overview

This repository holds the WooCommerce Attribute Standardizer Plugin, a custom solution designed to address specific issues with user-submitted product attribute data within the WooCommerce WordPress ecosystem.

## Problem Statement

My client operates a unique WooCommerce setup that allows users to upload their own products using a form created with WordPress User Frontend. This form has various input fields and options for defining product attributes. However, unlike typical implementations, these attribute fields were open inputs instead of a selection of predefined attributes.

As a result, users have been inputting attribute data in various formats, leading to data inconsistency. For instance, instead of simple 'Blue' or 'Brown' entries for color attributes, we have received descriptions such as 'Blue plastic' or 'brown lid'. This inconsistency has caused problems in product categorization and filtering.

Despite the evident problems, the client wanted to keep the existing form structure, prompting the need for a new solution that standardizes the attribute data while preserving the original user submission process.

## Solution - WooCommerce Attribute Standardizer Plugin

The WooCommerce Attribute Standardizer Plugin is designed to parse all user-submitted product attributes and standardize them against a predefined set of common values. By looping through all the products and their attributes, the plugin identifies any resemblance to the predefined values within the user-inputted data.

Upon finding a match, it adds the standardized value to a new attribute, prefixed by 'custom_'. For instance, the 'custom_color' attribute would contain the standardized color information extracted from the user's input. The plugin maintains the original user-inputted data, ensuring that the raw data remains accessible if required.

## Key Features

1. **Attribute Standardization**: Compares user-inputted attribute values with predefined common values, standardizing data across all products.
2. **New Attribute Generation**: Creates new standardized attributes prefixed with 'custom_', preserving original user-inputted data.
3. **Scheduled Operation**: Includes a feature to automatically schedule and process a certain number of products daily, reducing the load on server resources.

## Installation

1. Download the plugin from this repository.
2. Upload it to your WordPress plugins directory.
3. Activate the plugin from your WordPress admin dashboard.

Once activated, a new menu item will appear in the admin dashboard under the WooCommerce menu. You can initiate the standardization process manually or check the standardization status from this menu.

Please note that this plugin requires the WooCommerce plugin to be installed and activated on your WordPress site.

## GitHub Considerations

We encourage contributors to fork the repository, make changes, and create pull requests to improve the plugin's functionality further.

## Conclusion

The WooCommerce Attribute Standardizer Plugin addresses a real-world issue of ensuring data consistency in a user-generated product attribute scenario. It demonstrates the customizability of WooCommerce, promoting better data management, improved SEO, and enhanced user experience.
