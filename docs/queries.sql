SELECT id, coords_y, coords_x,
SQRT(
POW(111200 * (coords_y - 48.937), 2) +
POW(111200 * (5.415 - coords_x) * COS(5.415 / 57.3), 2)) AS distance
FROM (SELECT w.id as id, w.coords_x, w.coords_y FROM works as w WHERE w.coords_x IS NOT NULL) as w
HAVING distance < 500 ORDER BY distance

(SELECT id, coords_x, coords_y, title
FROM works
WHERE coords_x IS NOT NULL)
UNION
(SELECT w.id, o.coords_x, o.coords_y, w.title
FROM works as w, oeuvres as o
WHERE w.oeuvre_id = o.id
AND w.oeuvre_id IS NOT NULL)