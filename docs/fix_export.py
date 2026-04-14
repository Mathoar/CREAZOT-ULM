path = "/root/CREAZOT-ULM/api/src/Service/Export/PrestationExportFilter.php"
content = open(path).read()

# Fix 1: Handle null vol duration at line 78
old = "$volDuree = $this->getVolDurationToHourMinute($vol->getDuree());"
new = "$volDuree = $vol->getDuree() !== null ? $this->getVolDurationToHourMinute($vol->getDuree()) : '00:00';"
if old in content:
    content = content.replace(old, new)
    print("Fix 1: null vol duration OK")

# Fix 2: Make getVolDurationToHourMinute accept nullable
old2 = "private function getVolDurationToHourMinute(float $duration): string"
new2 = "private function getVolDurationToHourMinute(?float $duration): string"
if old2 in content:
    content = content.replace(old2, new2)
    print("Fix 2: nullable float OK")

# Fix 3: Handle null in getVolDurationToHourMinute body
old3 = """    {
        $hours = floor($duration);
        $minutes = round(($duration - $hours) * 100);
        return sprintf('%02d:%02d', $hours, $minutes);
    }"""
new3 = """    {
        if ($duration === null) return '00:00';
        $hours = floor($duration);
        $minutes = round(($duration - $hours) * 100);
        return sprintf('%02d:%02d', $hours, $minutes);
    }"""
if old3 in content:
    content = content.replace(old3, new3, 1)
    print("Fix 3: null guard in body OK")

# Fix 4: Same for getDecimalToHourMinute
old4 = "private function getDecimalToHourMinute(float $decimalDuration): string"
new4 = "private function getDecimalToHourMinute(?float $decimalDuration): string"
if old4 in content:
    content = content.replace(old4, new4)
    print("Fix 4: nullable decimal OK")

open(path, "w").write(content)
print("Done!")
