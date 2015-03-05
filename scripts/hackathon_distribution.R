# GBIF / Species 2000 hackathon, March 2015
# Script created 2015-03-04 by Dag Endresen, updated 2015-03-05

setwd("/Users/dag/workspace/r/gbif") # Set working directory
# http://ropensci.org/tutorials/rgbif_tutorial.html
#install.packages("rgbif")
require(rgbif) # interface to GBIF API v1

# Read (national) species checklist into R.
# stringsAsFactors=F to read species names into dataframe as plain text (not factors)
checklist <- read.delim("./checklist_NL.txt", header=TRUE, dec=".", stringsAsFactors=FALSE)

sp <- cbind(checklist, data.frame(taxonKey = 0, occCount = 0, occCountNL = 0))

## Test - exact name match for GBIF backbone crashes (!) for some names
#for (i in 1:10) { sp$taxonKey[i] <- name_backbone(name=sp$name[i])$speciesKey } # GBIF taxonKey

## Fuzzy name_suggest match for getting taxonKey seems to work better
for (i in 1:10) { sp$taxonKey[i] <- name_suggest(q=sp$scientificName[i], limit=1)$key } # column 8 taxonKey
for (i in 10:1000) { sp$taxonKey[i] <- name_suggest(q=sp$scientificName[i], limit=1)$key } # column 8 taxonKey
for (i in 1000:42230) { sp$taxonKey[i] <- name_suggest(q=sp$scientificName[i], limit=1)$key } # column 8 taxonKey

## GBIF taxonKey => occCount
for (i in 1:100) { sp$occCount[i] <- occ_count(sp$taxonKey[i]) } # occ of taxon
for (i in 100:42230) { sp$occCount[i] <- occ_count(sp$taxonKey[i]) } # occ of taxon

## GBIF taxonKey => occCountNL
# http://api.gbif.org/v1/occurrence/search?taxonKey=5754770&country=NL
#sp <- cbind(sp, data.frame(occCountNL = 0))
for (i in 1:100) { sp$occCountNL[i] <- occ_count(sp$taxonKey[i], country='NL') } # occ in NL
for (i in 100:42230) { sp$occCountNL[i] <- occ_count(sp$taxonKey[i], country='NL') } # occ in NL

## GBIF taxonKey => occCountNLspecimen
# http://api.gbif.org/v1/occurrence/search?taxonKey=5754770&country=NL&basisOfRecord=PRESERVED_SPECIMEN
sp <- cbind(sp, data.frame(occCountNLspecimen = 0))
for (i in 1:100) { sp$occCountNLspecimen[i] <- occ_count(sp$taxonKey[i], country='NL', basisOfRecord='PRESERVED_SPECIMEN') } # occ in NL specimens
for (i in 100:42230) { sp$occCountNLspecimen[i] <- occ_count(sp$taxonKey[i], country='NL', basisOfRecord='PRESERVED_SPECIMEN') } # occ in NL

## GBIF taxonKey => occCountNLobservation
# http://api.gbif.org/v1/occurrence/search?taxonKey=5754770&country=NL&basisOfRecord=OBSERVATION
sp <- cbind(sp, data.frame(occCountNLobservation = 0))
for (i in 1:100) { sp$occCountNLobservation[i] <- occ_count(sp$taxonKey[i], country='NL', basisOfRecord='OBSERVATION') } # occ in NL specimens
for (i in 100:42230) { sp$occCountNLobservation[i] <- occ_count(sp$taxonKey[i], country='NL', basisOfRecord='OBSERVATION') } # occ in NL

## GBIF taxonKey => occCountNLsince2000
# http://api.gbif.org/v1/occurrence/search?taxonKey=5754770&country=NL&year=2000,2020
sp <- cbind(sp, data.frame(occCountNLsince2000 = 0))
for (i in 1:100) { sp$occCountNLsince2000[i] <- occ_count(sp$taxonKey[i], country='NL', from='2000', to='2020') } # occ in NL specimens

## GBIF taxonKey => occCountNLsince1950
# http://api.gbif.org/v1/occurrence/search?taxonKey=5754770&country=NL&year=1950,2020
sp <- cbind(sp, data.frame(occCountNLsince1950 = 0))
for (i in 1:100) { sp$occCountNLsince1950[i] <- occ_count(sp$taxonKey[i], country='NL', from='1950', to='2020') } # occ in NL specimens



## Write results to text file
write.table(sp, file="./checklist_NL_taxonKey_occCounts.txt", sep="\t", col.names=NA, qmethod="double")



#############################
##### TEMP code for testing
#sp <- read.delim("./checklist_NL_taxonKey.txt", header=TRUE, dec=".", stringsAsFactors=FALSE)
#sp <- sp[,2:11]
#sp <- cbind(sp, data.frame(occCountNL = 0))

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
