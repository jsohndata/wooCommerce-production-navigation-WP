# WooCommerce Loop Navigation with Excluded Categories

Custom WooCommerce snippet that adds “Previous” and “Next” navigation buttons to single product pages.  
- Loops through the entire product catalog (published products only).  
- Excludes products within manually defined category IDs.  
- Wrap-around navigation: clicking "Next" on the last product takes you to the first, and vice versa.  
- Buttons inherit WooCommerce theme styling (using `.button` / `.woocommerce-button` classes).  
- Works with Code Snippets plugin or your child theme’s `functions.php`.  

---

## Working Sample
[Sansefuria.com](https://sansefuria.com/plants/alocasia-macrorrhiza-lutea-large-rbalo00/)

---

## Features

- Manual exclusion of categories via an array of category IDs.
- Looping navigation across product catalog with wrap-around behavior.
- WordPress-native `<button>` elements styled by WooCommerce theme.
- No external dependencies; pure PHP snippet.

---

## Requirements

- WordPress with WooCommerce installed.
- One of the following:
  - [Code Snippets plugin](https://wordpress.org/plugins/code-snippets/) (recommended)
  - Or access to your child theme’s `functions.php`

---

## Installation

### Option 1: Using Code Snippets (Recommended)

1. Install and activate the **Code Snippets** plugin:  
   *Dashboard → Plugins → Add New → Search for “Code Snippets”*
2. Go to **Snippets → Add New**.
3. Name it: `WooCommerce Product Loop Navigation with Exclusions`.
4. Paste the PHP code from the **Code** section below.
5. Set **Run snippet everywhere**.
6. Save and **Activate**.

### Option 2: Add to `functions.php` (Child Theme Only)

1. Open your child theme’s `functions.php`.
2. Paste the PHP code at the **end** of the file.
3. Save the file.

---

## Usage

1. Add or locate products in your WooCommerce store.
2. In the snippet or code, set the category IDs you want to exclude:

```php
$excluded_category_ids = array(12, 34, 56);
