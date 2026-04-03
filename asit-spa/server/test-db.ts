import { PrismaClient } from '@prisma/client';

async function main() {
  const url = 'mysql://root:@127.0.0.1:3306/asadvanceit';
  console.log('Testing direct hardcoded connection to:', url);
  const prisma = new PrismaClient({
    datasourceUrl: url,
  });
  try {
    await prisma.$connect();
    console.log('SUCCESS: Connected to database');
    const usersCount = await prisma.users.count();
    console.log('Users count:', usersCount);
  } catch (err) {
    console.error('FAILURE: Could not connect');
    console.error(err);
  } finally {
    await prisma.$disconnect();
  }
}

main();
