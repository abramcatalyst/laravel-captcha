# PowerShell script to merge dev to main and push to GitHub
$env:GIT_PAGER = ""
$env:PAGER = ""

Write-Host "🔄 Checking current branch..." -ForegroundColor Cyan
$currentBranch = git rev-parse --abbrev-ref HEAD
Write-Host "Current branch: $currentBranch" -ForegroundColor Yellow

if ($currentBranch -ne "dev") {
    Write-Host "⚠️  Switching to dev branch..." -ForegroundColor Yellow
    git checkout dev
}

Write-Host "`n📋 Staging all changes..." -ForegroundColor Cyan
git add -A

Write-Host "`n💾 Committing changes..." -ForegroundColor Cyan
git commit -m "chore: Production-ready v1.0.0 - Remove beta warnings, update stability" 2>&1 | Out-Null

Write-Host "`n🔄 Switching to main branch..." -ForegroundColor Cyan
git checkout main

Write-Host "`n🔀 Merging dev into main..." -ForegroundColor Cyan
git merge dev -m "Merge production-ready v1.0.0 from dev branch

- Updated README: Removed beta warnings
- Updated composer.json: Changed stability to stable, added version 1.0.0
- Updated CHANGELOG: Added v1.0.0 production release
- Cleaned up temporary files
- Package is now production-ready"

Write-Host "`n🏷️  Creating release tag v1.0.0..." -ForegroundColor Cyan
git tag -a v1.0.0 -m "Production release v1.0.0 - Stable and ready for production use"

Write-Host "`n📤 Pushing to GitHub..." -ForegroundColor Cyan
Write-Host "Pushing main branch..." -ForegroundColor Yellow
git push origin main

Write-Host "Pushing tags..." -ForegroundColor Yellow
git push origin v1.0.0

Write-Host "`n✅ Production release v1.0.0 deployed to GitHub!" -ForegroundColor Green
Write-Host "🎉 All done!" -ForegroundColor Green

