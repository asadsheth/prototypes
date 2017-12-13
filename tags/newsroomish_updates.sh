cp vibes.html ../../newsroomish/docs/
cp caches/*.* ../../newsroomish/docs/caches/
cd ../../newsroomish/docs/caches/
git add *.*
git commit -aq -m autoupdated
git push
cd -

cp vibes.html ../../newsroomish-asadsheth/docs/
cp caches/*.* ../../newsroomish-asadsheth/docs/caches/
cd ../../newsroomish-asadsheth/docs/caches/
git add *.*
git commit -aq -m autoupdated
git push
cd -
