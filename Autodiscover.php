<?php
/**
 * This script:
 *  1. Detects the domain (HTTP_HOST) automatically
 *  2. Generates autodiscover.xml (Outlook) and autoconfig.xml (Thunderbird)
 *  3. Updates or creates .htaccess rules for auto-discover
 *  4. Uses:
 *     - IMAP on port 993 with SSL/TLS
 *     - SMTP on port 587 with STARTTLS (SSL=off in Outlook terms)
 *
 * Usage:
 *   1. Upload to public_html/setup_autoconfig.php
 *   2. Visit https://yourdomain.com/setup_autoconfig.php in a browser
 *   3. Delete this file when done (for security).
 */

// ---------------------------------------------------------------------
// STEP 1: Detect domain
// ---------------------------------------------------------------------
$domain = $_SERVER['HTTP_HOST'];
// Remove "www." if present
$domain = preg_replace('/^www\./', '', $domain);

// Paths for XML files
$autodiscoverFile = __DIR__ . '/autodiscover.xml';
$autoconfigFile   = __DIR__ . '/autoconfig.xml';

// ---------------------------------------------------------------------
// STEP 2: Build the XML content
// ---------------------------------------------------------------------
// Outlook (Autodiscover) --> IMAP (993, SSL=on), SMTP (587, SSL=off => STARTTLS)
$autodiscoverXml = <<<XML
<?xml version="1.0" encoding="utf-8"?>
<Autodiscover xmlns="http://schemas.microsoft.com/exchange/autodiscover/responseschema/2006">
  <Response>
    <Account>
      <AccountType>email</AccountType>
      <Action>settings</Action>
      <!-- IMAP Settings -->
      <Protocol>
        <Type>IMAP</Type>
        <Server>mail.$domain</Server>
        <Port>993</Port>
        <SSL>on</SSL>
        <AuthRequired>on</AuthRequired>
      </Protocol>
      <!-- SMTP Settings (STARTTLS) -->
      <Protocol>
        <Type>SMTP</Type>
        <Server>mail.$domain</Server>
        <Port>587</Port>
        <!-- Setting SSL=off on port 587 indicates STARTTLS to Outlook -->
        <SSL>off</SSL>
        <AuthRequired>on</AuthRequired>
      </Protocol>
    </Account>
  </Response>
</Autodiscover>
XML;

// Thunderbird / Other Clients (Autoconfig) --> IMAP (993, SSL/TLS), SMTP (587, STARTTLS)
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
// STEP 3: Write the XML files
// ---------------------------------------------------------------------
file_put_contents($autodiscoverFile, $autodiscoverXml);
file_put_contents($autoconfigFile, $autoconfigXml);

// ---------------------------------------------------------------------
// STEP 4: Update or create .htaccess
// ---------------------------------------------------------------------
$htaccessPath = __DIR__ . '/.htaccess';

// Rules to ensure autodiscover requests hit our XML files
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
    // If .htaccess doesn't exist, create one with our rules
    file_put_contents($htaccessPath, $rulesToAdd . PHP_EOL);
} else {
    // If it exists, only add rules if they're missing
    $htaccessContent = file_get_contents($htaccessPath);
    if (strpos($htaccessContent, '# BEGIN Autodiscover') === false) {
        // Prepend or append the rules
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

if (isset($newHtaccess)) {
    echo "<p>The required rewrite rules have been <strong>added</strong> to <code>.htaccess</code>.</p>";
} else {
    echo "<p>The required rewrite rules were <strong>already present</strong> in <code>.htaccess</code>.</p>";
}

echo "<p style='color:red;'><strong>IMPORTANT:</strong> Delete this <code>setup_autoconfig.php</code> file after confirming everything works.</p>";
?>
