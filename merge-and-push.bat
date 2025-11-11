@echo off
set GIT_PAGER=
set PAGER=

echo Checking current branch...
for /f "tokens=*" %%i in ('git rev-parse --abbrev-ref HEAD') do set CURRENT_BRANCH=%%i
echo Current branch: %CURRENT_BRANCH%

if not "%CURRENT_BRANCH%"=="dev" (
    echo Switching to dev branch...
    git checkout dev
)

echo.
echo Staging all changes...
git add -A

echo.
echo Committing changes...
git commit -m "chore: Production-ready v1.0.0 - Remove beta warnings, update stability" 2>nul

echo.
echo Switching to main branch...
git checkout main

echo.
echo Merging dev into main...
git merge dev -m "Merge production-ready v1.0.0 from dev branch" --no-edit

echo.
echo Creating release tag v1.0.0...
git tag -a v1.0.0 -m "Production release v1.0.0 - Stable and ready for production use"

echo.
echo Pushing to GitHub...
echo Pushing main branch...
git push origin main

echo Pushing tags...
git push origin v1.0.0

echo.
echo Production release v1.0.0 deployed to GitHub!
echo All done!

