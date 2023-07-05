import sys
from nltk.corpus import wordnet
from statistics import mean
import json

preferences = sys.argv[2].split('|')
documents = json.loads(sys.argv[1])
result = {}
for doc in documents:
    wup = []
    # f = open("demofile3.txt", "a")
    # f.write(f"DOCUMENT {doc}:\n")
    # f.close()
    for keyw in documents[doc]:
        # f = open("demofile3.txt", "a")
        # f.write(f"KEYWORD {keyw}:\n")
        # f.close()
        for pref in preferences:
            # f = open("demofile3.txt", "a")
            # f.write(f"PREFERENCE {pref}:\n")
            # f.close()
            prefSyn = wordnet.synsets(pref)
            keySyn = wordnet.synsets(keyw)
            if len(prefSyn) > 0 and len(keySyn) > 0:
                for ks in keySyn:
                    for ps in prefSyn:
                        wsm = ps.wup_similarity(ks)
                        # f = open("demofile3.txt", "a")
                        # f.write(f"{ps} - {ks} : {wsm} \n")
                        # f.close()
                        wup.append(wsm)
    if len(wup) > 0:
        wupAvg = mean(wup)
        result[doc] = wupAvg
print(json.dumps(result))

# for doc in documents:
#     wup = []
#     for keyw in documents[doc]:
#         for pref in preferences:
#             if len(wordnet.synsets(pref)) > 0 and len(wordnet.synsets(keyw)) > 0:
#                 prefSyn = wordnet.synsets(pref)[0]
#                 keySyn = wordnet.synsets(keyw)[0]
#                 wup.append(prefSyn.wup_similarity(keySyn))
#     if len(wup) > 0:
#         wupAvg = mean(wup)
#         result[doc] = wupAvg
# print(json.dumps(result))


# for pref in preferences:
#   for doc in documents:
#      print(doc)
#      print("\n")
#      print("_________________________________________________________")
    # if len(wordnet.synsets(pref)) and len(wordnet.synsets(doc)):
    #   syn1 = wordnet.synsets(pref)[0]
    #   syn2 = wordnet.synsets(doc)[0]
    #   wup.append(syn1.wup_similarity(syn2))


# for pref in sys.argv[1].split('|'):
#   for doc in sys.argv[1].split('|'):
#     if len(wordnet.synsets(pref)) and len(wordnet.synsets(doc)):
#       syn1 = wordnet.synsets(pref)[0]
#       syn2 = wordnet.synsets(doc)[0]
#       wup.append(syn1.wup_similarity(syn2))

# if len(wup):
#   wupAvg = mean(wup)
#   print(wupAvg)

# print(wordnet.synsets(sys.argv[1]))

# syn1 = wordnet.synsets('php')

# syn2 = wordnet.synsets('cat')[0]

# print ("hello name :  ", syn1.name())
# print ("selling name :  ", syn2.name())
# print(syn1.wup_similarity(syn2))
# keywords = sys.argv[1].split('|')
# preferences = sys.argv[2].split('|')
