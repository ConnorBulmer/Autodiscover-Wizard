<?php
/**
 * This script:
 *  1. Detects the domain (HTTP_HOST) automatically
 *  2. Generates autodiscover.xml and autoconfig.xml
 *  3. Updates or creates an .htaccess file to redirect requests
 *     to these XML files.
 *
 *  USAGE:
 *    1. Upload to public_html/setup_autoconfig.php
 *    2. Visit https://yourdomain.com/setup_autoconfig.php
 *    3. Delete this file when done (for security).
 */

// ---------------------------------------------------------------------
// STEP 1: Detect domain
// ---------------------------------------------------------------------
$domain = $_SERVER['HTTP_HOST'];
// Remove possible "www." prefix
$domain = preg_replace('/^www\./', '', $domain);

// Paths for your XML files
$autodiscoverFile = __DIR__ . '/autodiscover.xml';
$autoconfigFile   = __DIR__ . '/autoconfig.xml';

// ---------------------------------------------------------------------
// STEP 2: Build the XML content
// ---------------------------------------------------------------------
// Outlook (Autodiscover)
$autodiscoverXml = <<<XML
<?xml version="1.0" encoding="utf-8"?>
<Autodiscover xmlns="http://schemas.microsoft.com/exchange/autodiscover/responseschema/2006">
  <Response>
    <Account>
      <AccountType>email</AccountType>
      <Action>settings</Action>
      <Protocol>
        <Type>IMAP</Type>
        <Server>mail.$domain</Server>
        <Port>993</Port>
        <SSL>on</SSL>
        <AuthRequired>on</AuthRequired>
      </Protocol>
      <Protocol>
        <Type>SMTP</Type>
        <Server>mail.$domain</Server>
        <Port>587</Port>
        <SSL>on</SSL>
        <AuthRequired>on</AuthRequired>
      </Protocol>
    </Account>
  </Response>
</Autodiscover>
XML;

// Thunderbird / Other Clients (Autoconfig)
$autoconfigXml = <<<XML
<?xml version="1.0"?>
<clientConfig version="1.1">
  <emailProvider id="$domain">
    <domain>$domain</domain>
    <displayName>Your Mail</displayName>
    <incomingServer type="imap">
      <hostname>mail.$domain</hostname>
      <port>993</port>
      <socketType>SSL</socketType>
      <authentication>password-cleartext</authentication>
    </incomingServer>
    <outgoingServer type="smtp">
      <hostname>mail.$domain</hostname>
      <port>587</port>
      <socketType>STARTTLS</socketType>
      <authentication>password-cleartext</authentication>
      <default>true</default>
    </outgoingServer>
  </emailProvider>
</clientConfig>
XML;

// ---------------------------------------------------------------------
// STEP 3: Write the XML files to disk
// ---------------------------------------------------------------------
file_put_contents($autodiscoverFile, $autodiscoverXml);
file_put_contents($autoconfigFile, $autoconfigXml);

// ---------------------------------------------------------------------
// STEP 4: Update or create .htaccess
// ---------------------------------------------------------------------
$htaccessPath = __DIR__ . '/.htaccess';

// The rules we want to ensure are present:
$rulesToAdd = <<<HTA
# BEGIN Autodiscover
RewriteEngine On

# If "autodiscover/autodiscover.xml" is requested, serve autodiscover.xml
RewriteRule ^autodiscover/autodiscover.xml$ autodiscover.xml [L]

# If "autoconfig" is requested, serve autoconfig.xml
RewriteRule ^autoconfig$ autoconfig.xml [L]
# END Autodiscover
HTA;

if (!file_exists($htaccessPath)) {
    // If .htaccess doesn't exist, create one with our rules.
    file_put_contents($htaccessPath, $rulesToAdd . PHP_EOL);
} else {
    // If it exists, we only add our rules if they're missing.
    $htaccessContent = file_get_contents($htaccessPath);
    // Check if marker is already there
    if (strpos($htaccessContent, '# BEGIN Autodiscover') === false) {
        // Insert rules at the very top, or you can append at the bottom.
        // Typically we put them near the top so they take precedence,
        // but either approach can work. Adjust as needed.
        $newHtaccess = $rulesToAdd . PHP_EOL . $htaccessContent;
        file_put_contents($htaccessPath, $newHtaccess);
    }
}

// ---------------------------------------------------------------------
// STEP 5: Output success message
// ---------------------------------------------------------------------
echo "<h3>✅ Autodiscover / Autoconfig setup complete for <strong>$domain</strong></h3>";
echo "<p>autodiscover.xml and autoconfig.xml have been created in:</p>";
echo "<ul>
        <li><code>" . htmlentities($autodiscoverFile) . "</code></li>
        <li><code>" . htmlentities($autoconfigFile) . "</code></li>
      </ul>";
echo "<p>The required rewrite rules have been ";
echo (isset($newHtaccess)) ? "added to" : "already present in";
echo " <code>.htaccess</code>.</p>";
echo "<p style='color:red;'><strong>IMPORTANT:</strong> Delete this <code>setup_autoconfig.php</code> file when finished.</p>";
