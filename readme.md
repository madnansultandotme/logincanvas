# LoginCanvas Plugin Documentation

## Overview
LoginCanvas is a WordPress plugin that enhances the login page experience by adding support for random background images and header/footer integration. This plugin is compatible with WordPress 6.4.3+ and PHP 8.3+.

## Features
- Multiple background image support with random display
- Header and footer integration on the login page
- Customizer integration for easy configuration
- Responsive design
- Compatible with latest WordPress and PHP versions

## Installation
1. Upload the `logincanvas` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to Appearance → Customize → LoginCanvas Settings to configure

## Configuration

### Setting Up Background Images
1. Navigate to Appearance → Customize → LoginCanvas Settings
2. Click "Select Images" under Background Images
3. Upload or select multiple images from your media library
4. Images will be randomly displayed on each login page load

### Enabling Header and Footer
1. Go to Appearance → Customize → LoginCanvas Settings
2. Check the "Enable Header and Footer" option
3. Save your changes
4. Your site's header and footer will now appear on the login page

## Technical Details

### System Requirements
- WordPress 5.0 or higher
- PHP 7.2 or higher
- Modern web browser

### File Structure
```
logincanvas/
├── includes/
│   ├── class-logincanvas-customizer.php
│   ├── class-logincanvas-background.php
│   └── class-logincanvas-header-footer.php
├── assets/
│   └── css/
│       └── login-style.css
├── languages/
└── logincanvas.php
```

### Actions and Filters
The plugin provides several actions and filters for customization:

```php
// Modify background images array
add_filter('logincanvas_background_images', 'custom_background_images');

// Modify header output
add_filter('logincanvas_header_content', 'custom_header_content');

// Modify footer output
add_filter('logincanvas_footer_content', 'custom_footer_content');
```

## Troubleshooting

### Common Issues

1. **Images Not Displaying**
   - Check if images are properly uploaded
   - Verify file permissions
   - Ensure images are accessible via URL

2. **Header/Footer Not Showing**
   - Verify the option is enabled in Customizer
   - Check theme compatibility
   - Review server error logs

### Support
For support, please:
1. Check the documentation
2. Review WordPress forums
3. Submit an issue on GitHub
4. Contact plugin support

## Security

- All user inputs are sanitized
- Proper nonce verification
- Secure file handling
- XSS prevention measures

## Changelog

### Version 1.0.0
- Initial release
- Background image randomization
- Header/footer integration
- Customizer controls
## Team
Developed by:
- **Muhammad Adnan Sultan** ([LinkedIn](https://www.linkedin.com/in/dev-madnansultan/))
- **Jaweria Saddique** ([Portfolio](https://www.linkedin.com/in/jaweria-siddique1/))
- **Amna Abaidullah** ([GitHub](https://github.com/))

## License
GPL v2 or later. See [License](https://www.gnu.org/licenses/gpl-2.0.html) for details.