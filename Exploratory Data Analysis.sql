-- Exploratory Data Analysis in MySQL

SELECT *
FROM layoffs_staging2;

SELECT MAX(total_laid_off), MAX(percentage_laid_off)
FROM layoffs_staging2;

SELECT *
FROM layoffs_staging2
WHERE percentage_laid_off = 1
ORDER BY total_laid_off DESC;

SELECT company, SUM(total_laid_off)
FROM layoffs_staging2
GROUP BY company
ORDER BY 2 DESC;

SELECT MIN(`date`), MAX(`date`)
FROM layoffs_staging2;

SELECT stage, SUM(total_laid_off)
FROM layoffs_staging2
GROUP BY stage
ORDER BY 2 DESC;

SELECT SUBSTRING(`date`, 1,7) as `MONTH`, SUM(total_laid_off)
FROM layoffs_staging2 
WHERE SUBSTRING(`date`, 1,7) IS NOT NULL
GROUP BY `MONTH`
ORDER BY 1 asc;

WITH Rolling_Total AS 
(
SELECT SUBSTRING(`date`, 1,7) as `MONTH`, SUM(total_laid_off) as total_off
FROM layoffs_staging2 
WHERE SUBSTRING(`date`, 1,7) IS NOT NULL
GROUP BY `MONTH`
ORDER BY 1 asc
)
SELECT `MONTH`,total_off ,SUM(total_off) OVER(ORDER BY `MONTH`) AS rolling_total
FROM Rolling_Total
;

SELECT company, YEAR(`date`) ,SUM(total_laid_off)
FROM layoffs_staging2
GROUP BY company, YEAR(`date`)
ORDER BY 3 DESC;

WITH Company_Year(company, years, total_laid_off) as 
(
SELECT company, YEAR(`date`) ,SUM(total_laid_off)
FROM layoffs_staging2
GROUP BY company, YEAR(`date`)
), Company_Year_Ranking AS
(SELECT *, DENSE_RANK() OVER(PARTITION BY years ORDER BY total_laid_off DESC) as Ranking
FROM Company_year
WHERE years IS NOT NULL)
SELECT *
FROM Company_Year_Ranking
WHERE Ranking <= 5
;



