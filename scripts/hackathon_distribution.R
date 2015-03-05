# Leiden hackathon, March 2015, GBIF and Species2000
# Script created 2015-03-04 by Dag Endresen (CC-BY), updated 2015-03-05

# INPUT: (national) checklist with at least a column "scientificName" and a column "countryCode"
# Provided as tab delimited text (other formats require minor updating of this script...)
# Other columns such as sourceTaxonId or key in the (national) checklist will be maintained
# The order of columns here is not important.
#
# OUTPUT: the (national) checklist is annotated with GBIF taxonKey and some occurrence counts.
# Occurrence counts will be for data provided in checklist.txt, column scientificName and countryCode
#

setwd("/Users/dag/workspace/r/gbif") # Set working directory, MODIFY for your respective environment
# http://ropensci.org/tutorials/rgbif_tutorial.html
#install.packages("rgbif") # Install rgbif if not already in your R environment
require(rgbif) # load package rgbif, provides interface to the GBIF API v1

# Read (national) species checklist into R.
# stringsAsFactors=F to read species names into dataframe as plain text (not factors)
checklist <- read.delim("./checklist.txt", header=TRUE, dec=".", stringsAsFactors=FALSE)

# Annotate checklist with GBIF taxonKey
sp <- cbind(checklist, data.frame(taxonKey = 0))
n <- dim(sp)[1] # number of records in the (national) checklist of names

## Test - exact name match for GBIF backbone crashes (!) for some names
#for (i in 1:10) { sp$taxonKey[i] <- name_backbone(name=sp$name[i])$speciesKey } # GBIF taxonKey

## Fuzzy name_suggest match for getting taxonKey seems to work better
# NB! The fuzzy match might perhaps suggest a taxonKey that might be impropper to use...!
# Here we simply take the taxonKey from the first hit in the list of ranked suggestions...
for (i in 1:10) { sp$taxonKey[i] <- name_suggest(q=sp$scientificName[i], limit=1)$key } # taxonKey
for (i in 10:1000) { sp$taxonKey[i] <- name_suggest(q=sp$scientificName[i], limit=1)$key } # taxonKey
for (i in 1:n) { sp$taxonKey[i] <- name_suggest(q=sp$scientificName[i], limit=1)$key } # taxonKey

## GBIF taxonKey => occCount
# http://api.gbif.org/v1/occurrence/search?taxonKey=5754770
sp <- cbind(sp, data.frame(occCount = 0))
for (i in 1:100) { sp$occCount[i] <- occ_count(sp$taxonKey[i]) } # occ of taxon
for (i in 1:n) { sp$occCount[i] <- occ_count(sp$taxonKey[i]) } # occ of taxon

## GBIF taxonKey => occCountCou
# http://api.gbif.org/v1/occurrence/search?taxonKey=5754770&country=NL
sp <- cbind(sp, data.frame(occCountCou = 0))
for (i in 1:100) { sp$occCountCou[i] <- occ_count(sp$taxonKey[i], country=sp$countryCode[i]) } # occ in NL
for (i in 1:n) { sp$occCountCou[i] <- occ_count(sp$taxonKey[i], country=sp$countryCode[i]) } # occ in NL

## GBIF taxonKey => occCountCouSpecimen
# http://api.gbif.org/v1/occurrence/search?taxonKey=5754770&country=NL&basisOfRecord=PRESERVED_SPECIMEN
sp <- cbind(sp, data.frame(occCountCouSpecimen = 0))
for (i in 1:100) { sp$occCountCouSpecimen[i] <- occ_count(sp$taxonKey[i], country=sp$countryCode[i], basisOfRecord='PRESERVED_SPECIMEN') } # occ in NL specimens
for (i in 1:n) { sp$occCountCouSpecimen[i] <- occ_count(sp$taxonKey[i], country=sp$countryCode[i], basisOfRecord='PRESERVED_SPECIMEN') } # occ in NL

## GBIF taxonKey => occCountNLobservation
# http://api.gbif.org/v1/occurrence/search?taxonKey=5754770&country=NL&basisOfRecord=OBSERVATION
sp <- cbind(sp, data.frame(occCountCouObservation = 0))
for (i in 1:100) { sp$occCountCouObservation[i] <- occ_count(sp$taxonKey[i], country=sp$countryCode[i], basisOfRecord='OBSERVATION') } # occ in NL specimens
for (i in 1:n) { sp$occCountCouObservation[i] <- occ_count(sp$taxonKey[i], country=sp$countryCode[i], basisOfRecord='OBSERVATION') } # occ in NL

## GBIF taxonKey => occCountCouSince2000
# http://api.gbif.org/v1/occurrence/search?taxonKey=5754770&country=NL&year=2000,2020
sp <- cbind(sp, data.frame(occCountCouSince2000 = 0))
for (i in 1:100) { sp$occCountCouSince2000[i] <- occ_count(sp$taxonKey[i], country=sp$countryCode[i], from='2000', to='2020') } # occ in NL specimens
for (i in 1:n) { sp$occCountCouSince2000[i] <- occ_count(sp$taxonKey[i], country=sp$countryCode[i], from='2000', to='2020') } # occ in NL specimens

## GBIF taxonKey => occCountNLsince1950
# http://api.gbif.org/v1/occurrence/search?taxonKey=5754770&country=NL&year=1950,2020
sp <- cbind(sp, data.frame(occCountCouSince1950 = 0))
for (i in 1:100) { sp$occCountCouSince1950[i] <- occ_count(sp$taxonKey[i], country=sp$countryCode[i], from='1950', to='2020') } # occ in NL specimens
for (i in 1:n) { sp$occCountCouSince1950[i] <- occ_count(sp$taxonKey[i], country=sp$countryCode[i], from='1950', to='2020') } # occ in NL specimens



## Write results to text file
write.table(sp, file="./checklist_taxonKey_occCounts.txt", sep="\t", col.names=NA, qmethod="double")



#############################
##### TEMP code for testing
#sp <- read.delim("./checklist_NL_taxonKey.txt", header=TRUE, dec=".", stringsAsFactors=FALSE)
#sp <- read.delim("./checklist_taxonKey_occCounts.txt", header=TRUE, dec=".", stringsAsFactors=FALSE)
#sp <- sp[,2:11]
#sp <- cbind(sp, data.frame(countryCode = 'NL'))

name_backbone(name='Abacoproeces saltuum')$speciesKey ## => 2137144
name_backbone(name='Abacoproeces saltuum (L. Koch, 1872)')$speciesKey ## => 2137144

name_backbone(name='Abax ovalis')$speciesKey ## => 5754770
name_backbone(name='Abax ovalis (Duftschmid, 1812)')$speciesKey ## => 5754770
name_lookup(query='Abax ovalis', return="data", limit=2) ## => 
name_suggest(q='Abax ovalis', limit=1)$key ## => 5754770
name_suggest(q='Abax ovalis (Duftschmid, 1812)', limit=1)$key ## => 5754770

name_backbone(name='Achillea millefolium')$speciesKey ## => NULL
name_suggest(q='Achillea millefolium') ## => 
name_suggest(q='Achillea millefolium', limit=1)$key ## => 3120060
name_suggest(q='Achillea millefolium L.', limit=1)$key ## => 3120060
