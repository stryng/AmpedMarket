#What is AmpedMarket
AmpedMarket is an open source project forked from BitWasp which aims to allow people to quickly set up anonymous markets with integrated BitCoin management and escrow services. AmpedMarket is designed to be run as a Tor hidden service. As such it is developed with security and anonymity in mind.

This project is still in its very early stages. It is essentially BitWasp until it has been thoroughly document and verified, at which point changes will be made. Minor patches to existing components will be pushed back to BitWasp, however new/highly modified components will remain unique to this project (others can use the code in their own software if they wish). 

#Want To Get Involved?

Use the wiki on github if you want to get involved in the development, or send an email to amphetamine@tormail.org

Even just testing on different configurations can be a big help.

#Current High-priority Development Activities

- Reverse-engineer documentation from existing BitWasp structure
- Convert all SQL queries to use base64 encoding instead of escaping to mitigate
- Rewrite clean replacements to remove dependence on CodeIgniter code

#Security TODO List (don't use this software unless this list is empty!)
- Replace SQL queries with ones that use base64 encoding to eliminate risk of SQL injection (escaping is NOT safe in the world of unicode)
- Perform penetration testing at the application level

#Testing TODO List
- Come up with a list of potential webserver configurations
- Test multiple webserver configurations for security, reliability, penetration, denial-of-service, etc.

#Planned Features
- Integrated PGP through javascript to ensure maximum security
- Ability to link vendor information from other anonymous markets

#Installation and Configuration (At this stage identical to BitWasp)
To set up AmpedMarket, first make ./application/config and ./assets/images writable.

chmod 777 ./application/config
chmod 777 -R ./assets/images

And then visit the installer page to set up the marketplace.

# Support AmpedMarket Development
All money from donations go to fund AmpedMarket development. 
Bitcoin Address: 14B1cC1gMHYbAAoGMMyfiYNJDBQpeMTUz1

These contributions help out a great deal and allow for faster development with more thorough testing (which we take very seriously)

