$head_tag = @"
<head>
<!-- Google Tag Manager -->
<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
})(window,document,'script','dataLayer','GTM-TNRCGB35');</script>
<!-- End Google Tag Manager -->
"@

$body_tag = @"
`$1
<!-- Google Tag Manager (noscript) -->
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-TNRCGB35"
height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<!-- End Google Tag Manager (noscript) -->
"@

$files = @(
  "about.html", "blog.html", "contact.html", "enquiry.html",
  "gallery.html", "index.html", "parents-login.html", "programs.html",
  "staff-login.html", "testimonials.html",
  "blogs\cbse-school-ballari-investment.html",
  "blogs\pre-school-admission-ballari.html",
  "blogs\smart-classrooms-sports-safety.html",
  "programs\middle-school.html",
  "programs\pre-school.html",
  "programs\primary-school.html"
)

foreach ($file in $files) {
    $path = Join-Path $pwd $file
    if (Test-Path $path) {
        $content = Get-Content -Raw -Path $path -Encoding UTF8
        if ($content -notmatch "GTM-TNRCGB35") {
            $content = $content -replace "(?i)<head>", $head_tag
            $content = [regex]::Replace($content, '(?i)(<body[^>]*>)', $body_tag)
            Set-Content -Path $path -Value $content -Encoding UTF8
            Write-Host "Updated $file"
        } else {
            Write-Host "Skipped $file (already has tag)"
        }
    } else {
        Write-Host "File not found $file"
    }
}
