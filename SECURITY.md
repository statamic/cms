If you discover a security vulnerability in Statamic, please review the following guidelines before submitting a report. We take security very seriously, and we do our best to resolve security issues as quickly as possible.

## Guidelines
While working to identify potential security vulnerabilities in Statamic, we ask that you:

- **Privately** share any issues that you discover with us via statamic.com/support as soon as possible.
- Give us a reasonable amount of time to address any reported issues before publicizing them.
- Only report issues that are in scope.
- Provide a quality report with precise explanations and concrete attack scenarios.

## Scope
We are only interested in vulnerabilities that affect Statamic itself, tested against **your own local installation** of the software, running the latest version. You can install a local copy of Statamic by following these [installation instructions](https://statamic.dev/installing). Do not test against any Statamic installation that you don’t own, including [statamic.com](https:/statamic.com), [statamic.dev](https://statamic.dev), and [demo.statamic.com](https://demo.statamic.com).

### Potentially Qualifying Vulnerabilities

- [Cross-Site Scripting (XSS)](https://en.wikipedia.org/wiki/Cross-site_scripting)
- [Cross-Site Request Forgery (CSRF)](https://en.wikipedia.org/wiki/Cross-site_request_forgery)
- [Arbitrary Code Execution](https://en.wikipedia.org/wiki/Arbitrary_code_execution)
- [Privilege Escalation](https://en.wikipedia.org/wiki/Privilege_escalation)
- [SQL Injection](https://en.wikipedia.org/wiki/SQL_injection)
- [Session Hijacking](https://en.wikipedia.org/wiki/Session_hijacking)

### Non-Qualifying Vulnerabilities

- XSS vectors or bugs that rely on an unlikely user interaction (i.e. a privileged user attacking themselves or their own site)
- Reports from automated tools or scanners
- Theoretical attacks without actual proof of exploitability
- Attacks that can be guarded against by following our security recommendations.
- Server configuration issues outside of Statamic’s control
- [Denial of Service](https://en.wikipedia.org/wiki/Denial-of-service_attack) attacks
- [Brute force attacks](https://en.wikipedia.org/wiki/Brute-force_attack) (e.g. on password or email address)
- Username or email address enumeration
- Social engineering of Statamic staff or users of Statamic installations
- Physical attacks against Statamic installations
- Attacks involving physical access to a user’s device, or involving a device or network that is already seriously compromised (e.g. [man-in-the-middle attacks](https://en.wikipedia.org/wiki/Man-in-the-middle_attack))
- Attacks that are the result of a 3rd party Statamic addon should be reported to the addon’s author
- Attacks that are the result of a 3rd party library should be reported to the library maintainers
- Disclosure of tools or libraries used by Statamic and/or their versions
- Issues that are the result of a user doing something silly (like sharing their password publicly)
- Missing security headers which do not lead directly to a vulnerability via proof of concept
- Vulnerabilities affecting users of outdated/unsupported browsers or platforms
- Vulnerabilities affecting outdated versions of Statamic
- Any behavior that is clearly documented.
- Issues discovered while scanning a site you don’t own without permission
- Missing CSRF tokens on forms (unless you have a proof of concept, many forms either don't need CSRF or are mitigated in other ways) and "logout" CSRF attacks
- [Open redirects](https://cheatsheetseries.owasp.org/cheatsheets/Unvalidated_Redirects_and_Forwards_Cheat_Sheet.html)
